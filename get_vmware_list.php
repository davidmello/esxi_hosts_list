<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
<title> VMWare Host Listing</title>
</head>
<body>
<style>
body {
    font-family: "Verdana";
    font-size: 12px;
} 

p {
    font-family: "Verdana";
    font-size: 14px;
    font-weight: bold;
}
</style>
<?php
/*
        Lista servidores VMWARE de uma vmware
        @author david.mello@gmail.com   
        @date 2015-11-16 16:00
*/

$snmp_community = 'public';
$count = 0;

// VMWare Servers
$servers = array(
                'SERVER1' => 'Short Description (to be improved)', 
                'SERVER2' => 'Model: R710 | Service Tag: XXXXXXX',
                'SERVERN' => 'Model: R730 | Service Tag: YYYYYYY'

);

while ($server = key($servers)) {
        // faz um snmpwalk para pegar os OIDs para saber quantos hosts temos na VM
        $a = snmpwalkoid($server, $snmp_community, ".1.3.6.1.4.1.6876.2.1.1.2"); 

        $phisical_memory_tmp = snmp2_get ( $server, $snmp_community, 'SNMPv2-SMI::enterprises.6876.3.2.1.0' );
        $phisical_memory = str_replace( "\"", "", substr($phisical_memory_tmp, strrpos($phisical_memory_tmp, ':') + 1));
        $phisical_memory_gb = round($phisical_memory/1024/1024);

        $procs = snmp2_get( $server, $snmp_community, 'HOST-RESOURCES-MIB::hrDeviceDescr' );
        //$procts = preg_match( '/cpu/i', snmp2_get( $server, $snmp_community, 'HOST-RESOURCES-MIB::hrDeviceDescr' ) );
        $b = snmpwalkoid( $server, $snmp_community, 'HOST-RESOURCES-MIB::hrDeviceDescr' );
        $count_procs=0;
        foreach ( $b as $cpus ) { 
                if(preg_match('/cpu/i', $cpus)) {
                        $count_procs++;
                }
        }

        echo "<table border=\"1\" colspacing=\"0\" cellspacing=\"0\" width=\"1024\">\n";
        echo "<tr><td colspan=\"5\"><p> $server | Cores: $count_procs | Memory: $phisical_memory ($phisical_memory_gb GB) | $servers[$server]</p></td></tr>";
        echo "<tr>\n";
        echo "  <td><b>Nome Host</b></td><td style=\"width:500px\"><b>Kernel Version</b></td><td style=\"width:60px\"  align=\"center\"><b>CPUs</b></td><td style=\"width:70px\" align=\"center\"><b>RAM</b></td><td style=\"width:120px\"  align=\"center\"><b>Power Status</b></td>\n";
        echo "</tr>\n";
        $total_procs=0;
        $total_mem=0;
        $total_vms=0;
        for (reset($a); $i = key($a); next($a)) {
        //    echo "$i: $a[$i]<br />\n";
    
        echo "<tr>\n";
                $chave = substr($i, strrpos($i, '.') + 1); 
                $vm_nome_tmp    = snmp2_get ( $server , $snmp_community , '.1.3.6.1.4.1.6876.2.1.1.2.'.$chave );
                $vm_nome        = str_replace("\"", "", substr($vm_nome_tmp, strrpos($vm_nome_tmp, ':') + 1));


                $vm_kernel_version_tmp = snmp2_get ( $server , $snmp_community , '.1.3.6.1.4.1.6876.2.1.1.4.'.$chave );
                $vm_kernel_version = str_replace("\"", "", substr($vm_kernel_version_tmp, strrpos($vm_kernel_version_tmp, ':') + 1));


                $vm_memory_total_tmp   = snmp2_get ( $server , $snmp_community , '.1.3.6.1.4.1.6876.2.1.1.5.'.$chave );
                $vm_memory_total = str_replace("\"", "", substr($vm_memory_total_tmp, strrpos($vm_memory_total_tmp, ':') + 1));


                $vm_power_status_tmp = snmp2_get ( $server , $snmp_community , '.1.3.6.1.4.1.6876.2.1.1.6.'.$chave );
                $vm_power_status     = str_replace("\"", "", substr($vm_power_status_tmp, strrpos($vm_power_status_tmp, ':') + 1));


                $vm_proc_total_tmp   = snmp2_get ( $server , $snmp_community , '.1.3.6.1.4.1.6876.2.1.1.9.'.$chave );
                $vm_proc_total       = str_replace("\"", "", substr($vm_proc_total_tmp, strrpos($vm_proc_total_tmp, ':') + 1));



                echo "  <td>$vm_nome</td>
                        <td>$vm_kernel_version</td>
                        <td  align=\"center\">$vm_proc_total</td>
                        <td  align=\"center\">$vm_memory_total</td>
                        <td  align=\"center\">"; 
                        if($vm_power_status == " powered off") {
                            echo "<font color=\"red\">$vm_power_status</font>";
                        }
                        else {
                            echo "<font color=\"green\">$vm_power_status</font>";
                        } echo "</td>\n";
        echo "</tr>\n";
        $total_procs=$total_procs+$vm_proc_total;
        $total_mem=$total_mem+$vm_memory_total;
        $total_vms=$total_vms+1;
        }

        echo "<tr><td align=\"center\"><b>$total_vms VMs Instalada(s)</b></td><td></td><td align=\"center\"><b>$total_procs</b></td><td align=\"center\"><b>$total_mem</b></td><td></td></tr>";
        echo "</table>\n";
        echo "<br /><br />";
        next($servers);
}


?>
</body>
</html>
