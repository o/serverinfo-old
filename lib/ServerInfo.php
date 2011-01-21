<?php

/**
 * @package     Server Info PHP
 * @author      Osman Ungur <osmanungur@gmail.com>
 * @copyright   2009-2010 Osman Ungur (http://www.osman.gen.tr)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version     1.0.0b48
 * @link        http://code.google.com/p/serverinfo-php/
 */

class ServerInfo
{
    protected $config;
    protected $response;
    public $info;
    
    function __construct()
    {
        $this->setconfig();
        $this->checkfunctions();
    }
    
    private function setconfig()
    {
        global $config;
        foreach ($config as $key => $value) {
            $this->config->$key = $value;
        }
    }
    
    private function checkfunctions()
    {
        if (function_exists('fsockopen'))
            $this->info->sockenabled = TRUE;
        if (function_exists('shell_exec'))
            $this->info->shellenabled = TRUE;
        if (function_exists('apc_cache_info'))
            $this->info->apcenabled = TRUE;
    }
    
    private function getresponse($key, $value)
    {
        $this->response->$key = shell_exec($value);
    }
    
    public function percent($amount, $total, $decimal = 1)
    {
        return number_format(($amount / $total) * 100, $decimal);
    }
    
    public function formatbytes($bytes, $forapc = FALSE)
    {
        $units = array(
            'B',
            'KB',
            'MB',
            'GB',
            'TB'
        );
        $bytes = max($bytes, 0);
        if (!$forapc) {
            $bytes = $bytes * 1024;
        }
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    protected function getcpuinfo()
    {
        $this->getresponse('cpuinfo', 'cat /proc/cpuinfo');
        foreach (explode("\n", $this->response->cpuinfo) as $key => $value) {
            $values                                = explode(":", $value);
            $this->info->cpuinfo[trim($values[0])] = trim($values[1]);
        }
    }
    
    protected function getmeminfo()
    {
        $this->getresponse('meminfo', 'cat /proc/meminfo');
        foreach (explode("\n", $this->response->meminfo) as $key => $value) {
            $values                          = explode(":", $value);
            $values[1]                       = preg_replace("/ kB/", "", $values[1]);
            $this->info->meminfo[$values[0]] = trim($values[1]);
        }
        $this->info->meminfo['MemUsed'] = $this->info->meminfo['MemTotal'] - $this->info->meminfo['MemFree'];
    }
    
    protected function getloadavg()
    {
        $this->getresponse('loadavg', 'cat /proc/loadavg');
        $this->info->loadavg = explode(" ", $this->response->loadavg);
    }
    
    protected function getuptime()
    {
        $this->getresponse('uptime', 'cat /proc/uptime');
        $uptimeinfo = explode(" ", $this->response->uptime);
        $seconds    = $uptimeinfo[0];
        $periods    = array(
            'years' => 31556926,
            'months' => 2629743,
            'weeks' => 604800,
            'days' => 86400,
            'hours' => 3600,
            'minutes' => 60,
            'seconds' => 1
        );
        foreach ($periods as $period => $seconds_in_period) {
            if ($seconds >= $seconds_in_period) {
                $this->info->uptime[$period] = floor($seconds / $seconds_in_period);
                $seconds -= $this->info->uptime[$period] * $seconds_in_period;
            }
        }
    }
    
    protected function gethostname()
    {
        $this->getresponse('hostname', 'cat /proc/sys/kernel/hostname');
        $this->info->hostname = trim($this->response->hostname);
        if (!$this->info->hostname) {
            $this->info->hostname = getenv('SERVER_NAME');
        }
        $this->info->ipaddress      = gethostbyname($this->info->hostname);
        $this->info->serversoftware = str_replace("/", " ", $_SERVER['SERVER_SOFTWARE']);
    }
    
    protected function getunixname()
    {
        $this->getresponse('unixname', 'uname -a');
        $this->info->unixname = explode(" ", $this->response->unixname);
    }
    
    protected function getdistro()
    {
        $this->getresponse('distro', 'cat /etc/*-release');
        $this->info->distro = $this->response->distro;
        if (!$this->info->distro) {
            $this->info->distro = "Unknown";
        }
    }
    
    protected function getdiskfree()
    {
        $this->getresponse('diskfree', "df -k $1 | grep -v Filesystem| awk '{printf $1 \" \" $2 \" \" $3 \" \" $4 \" \" int($5)\",\"}'");
        $this->info->disks = explode(",", $this->response->diskfree);
        $this->info->disks = array_slice($this->info->disks, 0, $this->config->disks);
        foreach ($this->info->disks as $disk) {
            $this->info->diskfree[] = explode(" ", $disk);
        }
    }
    
    protected function getnetwork()
    {
        $this->getresponse('network', "cat /proc/net/dev $1 | grep eth0| awk '{printf $1 \" \" $9}'");
        $this->info->network = preg_replace("/eth0:/", "", $this->response->network);
        $this->info->network = explode(" ", $this->info->network);
    }
    
    protected function checkports()
    {
        foreach ($this->config->ports as $port => $service) {
            $fp = fsockopen($this->info->hostname, $port, $errno, $errstr, $this->config->timeout);
            if (!$fp) {
                $this->info->portstatus[] = array(
                    'domain' => $this->info->hostname,
                    'port' => $port,
                    'service' => $service,
                    'status' => 0,
                    'errstr' => $errstr,
                    'errno' => $errno
                );
            } else {
                $this->info->portstatus[] = array(
                    'domain' => $this->info->hostname,
                    'port' => $port,
                    'service' => $service,
                    'status' => 1
                );
            }
            fclose($fp);
        }
    }
    
    protected function getapcstats()
    {
        $time                                        = time();
        $apc_cache_info                              = apc_cache_info('opcode');
        $apc_user_cache_info                         = apc_cache_info('user', 1);
        $apc_memory_info                             = apc_sma_info();
        $this->info->apc_stats->total_memory         = $apc_memory_info['num_seg'] * $apc_memory_info['seg_size'];
        $this->info->apc_stats->available_memory     = $apc_memory_info['avail_mem'];
        $this->info->apc_stats->used_memory          = $this->info->apc_stats->total_memory - $this->info->apc_stats->available_memory;
        $this->info->apc_stats->request_rate         = sprintf("%.2f", ($apc_cache_info['num_hits'] + $apc_cache_info['num_misses']) / ($time - $apc_cache_info['start_time']));
        $this->info->apc_stats->user_request_rate    = sprintf("%.2f", ($apc_user_cache_info['num_hits'] + $apc_user_cache_info['num_misses']) / ($time - $apc_user_cache_info['start_time']));
        $this->info->apc_stats->cached_files         = $apc_cache_info['num_entries'];
        $this->info->apc_stats->cached_variables     = $apc_user_cache_info['num_entries'];
        $this->info->apc_stats->cached_file_size     = $apc_cache_info['mem_size'];
        $this->info->apc_stats->cached_variable_size = $apc_user_cache_info['mem_size'];
        $this->info->apc_stats->version              = phpversion('apc');
    }
    
    public function executeall()
    {
        if ($this->info->shellenabled) {
            $this->getcpuinfo();
            $this->getmeminfo();
            $this->getloadavg();
            $this->getuptime();
            $this->gethostname();
            $this->getunixname();
            $this->getdistro();
            $this->getdiskfree();
            $this->getnetwork();
        }
        if ($this->info->sockenabled) {
            $this->checkports();
        }
        if ($this->info->apcenabled) {
            $this->getapcstats();
        }
    }
    
}
