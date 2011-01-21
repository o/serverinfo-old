<?php
/**
 * @package     Server Info PHP
 * @author      Osman Ungur <osmanungur@gmail.com>
 * @copyright   2009-2010 Osman Ungur (http://www.osman.gen.tr)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version     1.0.0b22
 * @link        http://code.google.com/p/serverinfo-php/
 */

class HtmlView extends ServerInfo
{
    function __construct()
    {
        parent::__construct();
    }
    
    public function viewhead()
    {
        echo <<<HTML
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Server Info</title>
	<link href="style.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>
	<script type="text/javascript">\$(document).ready(function(){\$("#live").load("ajax.php");var refreshId=setInterval(function(){\$("#live").load('ajax.php?randval='+Math.random());},3000);});</script>
	</head>
	<body>
HTML;
    }
    
    
    public function viewheader()
    {
        $this->gethostname();
        $this->getcpuinfo();
        $this->getunixname();
        $this->getdistro();
        echo <<<HTML
	<div class='container'>
	<div class='header'><span class='left'>Server Info</span> <span class='right'>{$this->info->hostname} {$this->info->ipaddress}</span></div>
	<div class='clear'></div>
	<ul>
	<li>{$this->info->cpuinfo['model name']}, {$this->info->cpuinfo['cpu MHz']} MHz, {$this->info->cpuinfo['cache size']} Cache, {$this->info->cpuinfo['cpu cores']} Core <span class='right'>Processor</span></li>
	<li>{$this->info->distro} <span class='right'>Distribution</span></li>
	<li>{$this->info->unixname[0]} {$this->info->unixname[2]} {$this->info->unixname[12]} <span class='right'>Kernel</span></li>
	<li>{$this->info->serversoftware} <span class='right'>Software</span></li>
	</ul>
HTML;
    }
    
    public function viewuptime()
    {
        $this->getuptime();
        echo '<ul><li>';
        foreach ($this->info->uptime as $key => $value) {
            echo "$value $key ";
        }
        echo "<span class='right'>Uptime</span></li></ul>";
    }
    
    public function viewload()
    {
        $this->getloadavg();
        echo <<<HTML
	<h3>System Health</h3>
	<ul>
	<li><span class='gray'>1 Min : </span>{$this->info->loadavg[0]} <span class='gray'>5 Min : </span>{$this->info->loadavg[1]} <span class='gray'>15 Min : </span>{$this->info->loadavg[2]} <span class='right'>Load Averages</span></li>
	</ul>
HTML;
        $this->makebar($this->info->loadavg[0], $this->config->load_threshold);
    }
    
    public function viewmemory()
    {
        $this->getmeminfo();
        echo '<ul><li>';
        echo "<span class='gray'><strong>Memory</strong> Total :</span> " . $this->formatbytes($this->info->meminfo['MemTotal']) . " <span class='gray'>Free :</span> " . $this->formatbytes($this->info->meminfo['MemFree']) . " <span class='gray'><strong>Swap</strong> Total :</span> " . $this->formatbytes($this->info->meminfo['SwapTotal']) . " <span class='gray'>Free :</span> " . $this->formatbytes($this->info->meminfo['SwapFree']);
        echo "<span class='right'>Memory</span></li></ul>";
        $this->makebar($this->info->meminfo['MemUsed'], $this->info->meminfo['MemTotal']);
    }
    
    public function getajaxdiv()
    {
        echo <<<HTML
		<div id='live'></div>
HTML;
    }
    
    public function viewcheckedports()
    {
        $this->checkports();
        echo '<h3>Services</h3>';
        foreach ($this->info->portstatus as $ports) {
            if ($ports['status'] == 1) {
                echo "<div class='sbar success'><span class='gray'>$ports[domain]</span> $ports[port] <span class='right'>$ports[service]</span></div>";
            } else {
                echo "<div class='sbar error'><span class='gray'>$ports[domain]</span> $ports[port] $ports[errstr]<span class='right'>$ports[service]</span></div>";
            }
        }
    }
    
    public function viewdisks()
    {
        $this->getdiskfree();
        echo '<h3>Disks</h3>';
        foreach ($this->info->diskfree as $disks) {
            echo '<ul><li>';
            echo "<span class='gray'>Disk :</span> $disks[0] <span class='gray'>Total :</span> " . $this->formatbytes($disks[1]) . " <span class='gray'>Available :</span> " . $this->formatbytes($disks[3]) . " <span class='right'>% $disks[4]</span>";
            echo '</li></ul>';
            $this->makebar($disks[4]);
        }
    }
    
    public function viewapcstats()
    {
        $this->getapcstats();
        echo "<h3>Alternative PHP Cache " . $this->info->apc_stats->version . "</h3>";
        echo '<ul><li>';
        echo "<span class='gray'>Total :</span> " . $this->formatbytes($this->info->apc_stats->total_memory, TRUE) . " <span class='gray'>Used :</span> " . $this->formatbytes($this->info->apc_stats->used_memory, TRUE) . " <span class='gray'>Available :</span> " . $this->formatbytes($this->info->apc_stats->available_memory, TRUE) . " <span class='right'>Memory Usage</span>";
        echo '</li></ul>';
        $this->makebar($this->info->apc_stats->used_memory, $this->info->apc_stats->total_memory);
        echo '<ul>';
        echo "<li><span class='gray'>File :</span> " . $this->info->apc_stats->request_rate . " <span class='gray'>User :</span> " . $this->info->apc_stats->user_request_rate . " <span class='right'>Hits / per second</span></li>";
        echo "<li><span class='gray'>File :</span> " . $this->info->apc_stats->cached_files . " <span class='gray'>User :</span> " . $this->info->apc_stats->cached_variables . " <span class='right'>Cached</span></li>";
        echo "<li><span class='gray'>File :</span> " . $this->formatbytes($this->info->apc_stats->cached_file_size, TRUE) . " <span class='gray'>User :</span> " . $this->formatbytes($this->info->apc_stats->cached_variable_size, TRUE) . " <span class='right'>Memory</span></li>";
        echo '</ul>';
    }
    
    public function viewfooter()
    {
        echo <<<HTML
	<div class="footer">
	<a href="http://osman.gen.tr/server-info">http://osman.gen.tr/server-info</a> <span class='right'>Osman Üngür</span>
	</div>
	</div>
	</body>
	</html>
HTML;
    }
    
    public function makebar($amount, $total = 100)
    {
        $percent   = number_format(($amount / $total) * 100);
        $remaining = 100 - $percent;
        if ($percent < 10) {
            echo "<div class='success bar' style='width: 100%;'>% $percent</div>";
        } elseif ($percent >= 10 && $percent < 90) {
            echo "<div class='error bar' style='width: $percent%;'>% $percent</div>";
            echo "<div class='success bar' style='width: $remaining%;'>% $remaining</div>";
        } elseif ($percent >= 90 && $percent < 100) {
            echo "<div class='notice bar' style='width: 100%;'>% $percent</div>";
        } elseif ($percent >= 100) {
            echo "<div class='error bar' style='width: 100%;'>% $percent</div>";
        }
        echo "<div class='clear'></div>";
    }
    
}