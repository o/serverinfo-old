<?php
/**
 * @package     Server Info PHP
 * @author      Osman Ungur <osmanungur@gmail.com>
 * @copyright   2009-2010 Osman Ungur (http://www.osman.gen.tr)
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version     1.0.0b12
 * @link        http://osman.gen.tr/server-info
 */

class MobileView extends ServerInfo
{
    function __construct()
    {
        parent::__construct();
    }
    
    public function viewhead()
    {
        echo <<<HTML
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<title>Server Info</title>
	<meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
	<link href="mobile.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
HTML;
    }
    
    
    public function viewheader()
    {
        $this->gethostname();
        echo <<<HTML
	<div class='head'>Server Info</div>
	<div class='title'>Hostname</div>
	<ul>
	<li class='item'>{$this->info->hostname} {$this->info->ipaddress}</li>
	</ul>
HTML;
    }
    
    public function viewuptime()
    {
        $this->getuptime();
        echo "<div class='title'>Uptime</div>";
        echo "<ul><li class='item'>";
        foreach ($this->info->uptime as $key => $value) {
            echo "$value $key ";
        }
        echo "</li></ul>";
    }
    
    public function viewload()
    {
        $this->getloadavg();
        echo <<<HTML
	<div class='title'>Load Averages</div>
	<ul>
	<li class='item'><span class='gray'>1 Min : </span>{$this->info->loadavg[0]} <span class='gray'>5 Min : </span>{$this->info->loadavg[1]} <span class='gray'>15 Min : </span>{$this->info->loadavg[2]}</li>
	</ul>
HTML;
    }
    
    public function viewmemory()
    {
        $this->getmeminfo();
        echo "<div class='title'>Memory Usage</div>";
        echo "<ul><li class='item'>";
        echo "<span class='gray'><strong>Memory</strong> Total :</span> " . $this->formatbytes($this->info->meminfo['MemTotal']) . " <span class='gray'>Free :</span> " . $this->formatbytes($this->info->meminfo['MemFree']) . " <span class='gray'><strong>Swap</strong> Total :</span> " . $this->formatbytes($this->info->meminfo['SwapTotal']) . " <span class='gray'>Free :</span> " . $this->formatbytes($this->info->meminfo['SwapFree']);
        echo "</li></ul>";
    }
    
    public function viewcheckedports()
    {
        $this->checkports();
        echo "<div class='title'>Services</div>";
        foreach ($this->info->portstatus as $ports) {
            if ($ports['status'] == 1) {
                echo "<li class='item success'><span class='gray'>$ports[domain]</span> $ports[port] $ports[service]</li>";
            } else {
                echo "<li class='item error'><span class='gray'>$ports[domain]</span> $ports[port] $ports[service] $ports[errstr]</li>";
            }
        }
    }
    
    public function viewdisks()
    {
        $this->getdiskfree();
        echo "<div class='title'>Disks</div>";
        foreach ($this->info->diskfree as $disks) {
            echo "<ul><li class='item'>";
            echo "<span class='gray'>Disk :</span> $disks[0] <span class='gray'>Total :</span> " . $this->formatbytes($disks[1]) . " <span class='gray'>Available :</span> " . $this->formatbytes($disks[3]) . " <span class='right'>% $disks[4]</span>";
            echo '</li></ul>';
        }
    }
    
    public function viewapcstats()
    {
        $this->getapcstats();
        echo "<div class='title'>Alternative PHP Cache " . $this->info->apc_stats->version . "</div>";
        echo "<ul><li class='item'>";
        echo "<span class='gray'>Total :</span> " . $this->formatbytes($this->info->apc_stats->total_memory, TRUE) . " <span class='gray'>Used :</span> " . $this->formatbytes($this->info->apc_stats->used_memory, TRUE) . " <span class='gray'>Available :</span> " . $this->formatbytes($this->info->apc_stats->available_memory, TRUE);
        echo '</li></ul>';
    }
    
    public function viewfooter()
    {
        echo <<<HTML
	<ul>
	<li class='item footer'><a href="http://osman.gen.tr/server-info">http://osman.gen.tr/server-info</a> <span class='gray'>Osman Üngür</span></li>
	</ul>
	</body>
	</html>
HTML;
    }
    
}