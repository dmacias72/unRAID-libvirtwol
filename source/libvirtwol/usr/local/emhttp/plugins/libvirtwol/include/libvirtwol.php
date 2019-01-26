<?
$config_file        = '/boot/config/domain.cfg';
$libvirt_cfg     = parse_ini_file($config_file);
$libvirt_python  = (!file_exists('/usr/bin/python'));
$wol_service = isset($libvirt_cfg['WOL']) ? htmlspecialchars($libvirt_cfg['WOL']) : 'disable';
$wol_running = (intval(trim(shell_exec( "ps ax | grep 'libvirtwol.py' | grep -v grep &>/dev/null && echo 1 || echo 0 2> /dev/null" ))) === 1);
$wol_status_running     = '<span class="green">Running</span>';
$wol_status_stopped     = '<span class="orange">Stopped</span>';
$wol_status  = ($wol_running) ? $wol_status_running : $wol_status_stopped;

$vbmc_service = isset($libvirt_cfg['VBMC']) ? htmlspecialchars($libvirt_cfg['VBMC']) : 'disable';
$vbmc_running = (intval(trim(shell_exec( "ps ax | grep 'vbmcd' | grep -v grep &>/dev/null && echo 1 || echo 0 2> /dev/null" ))) === 1);
$vbmc_status_running     = '<span class="green">Running</span>';
$vbmc_status_stopped     = '<span class="orange">Stopped</span>';
$vbmc_status  = ($vbmc_running) ? $vbmc_status_running : $vbmc_status_stopped;
?>
<div class="advanced">
<div id="title" style="white-space:normal;"><span><i class="icon fa fa-bell"></i>Libvirt wake on lan</span>
<span class="status"> Status: <?=$wol_status;?></span></div>
<div id="wolform"></div>
<div id="wolinput">
<input type="hidden" name="#file" value="/boot/config/domain.cfg" />
<input type="hidden" id="wolcommand" name="#command" value="" />
<dl>
    <dt>Enable Wake On Lan:</dt>
    <dd>
        <select id="WOL" name="WOL" size="1">
            <?=mk_option($wol_service, "disable", "No");?>
            <?=mk_option($wol_service, "enable", "Yes");?>
        </select>
    </dd>
</dl>
<blockquote class="inline_help">
    <p>Enable wake on lan for virtual machines.  Allows you to start a virtual machine by sending a WOL packet with the MAC address of the virtual machine to the unRAID server</p>
    <p> or broadcast the packet on the same network. It listens on your VM bridge for UDP 7 or 9 and ether proto 0x0842.</p>
</blockquote>
<dl>
    <dt>&nbsp;</dt>
    <dd><input id="wolApply" type="button" value="Apply"><input type="button" value="Done" onClick="done()"></dd>
</dl>
</div>

<div id="title" style="white-space:normal;"><span><i class="icon fa fa-cogs"></i>Libvirt Virtual BMC</span>
<span class="status"> Status: <?=$vbmc_status;?></span></div>
<div id="vbmcform"></div>
<div id="vbmcinput">
<input type="hidden" name="#file" value="/boot/config/domain.cfg" />
<input type="hidden" id="vbmccommand" name="#command" value="" />
<dl>
    <dt>Enable Virtual BMC:</dt>
    <dd>
        <select id="VBMC" name="VBMC" size="1">
            <?=mk_option($vbmc_service, "disable", "No");?>
            <?=mk_option($vbmc_service, "enable", "Yes");?>
        </select>
    </dd>
</dl>
<blockquote class="inline_help">
    <p>Enable BMC for virtual machines.  A virtual BMC for controlling virtual machines using IPMI commands.</p>
</blockquote>
<dl>
    <dt>&nbsp;</dt>
    <dd><input id="vbmcApply" type="button" value="Apply"><input type="button" value="Done" onClick="done()"></dd>
</dl>
</div>

</div>
<script>
$(function(){
    // check for python 2
    if ("<?=$libvirt_python;?>"){
        swal({title:'Python 2 Not Installed',text:'Libvirt Wake On Lan requires python 2 from the NerdPack plugin https://raw.githubusercontent.com/dmacias72/unRAID-NerdPack/master/plugin/NerdPack.plg',type:'error',closeOnConfirm: true});
    }

    // dynamix plugin update api
    <?if (function_exists('plugin_update_available') && $version = plugin_update_available('libvirtwol')):?>
        showNotice('Wake on Lan <?=htmlspecialchars($version);?> available. <a>Update</a>','libvirtwol');
        $('#user-notice a').on('click', function () {
            $('#user-notice').empty();
        });
    <?endif;?>

    checkWOL();
    $('#WOL').on('change', checkWOL);
    $('#wolApply').on('click', verifyWOL);
    $('#wolform').html('<form id="wolsettings" name="wolsettings" method="POST" action="/update.php" target="progressFrame"></form>');
    $('#wolinput').appendTo('#wolsettings');

    checkVBMC();
    $('#VBMC').on('change', checkVBMC);
    $('#vbmcApply').on('click', verifyVBMC);
    $('#vbmcform').html('<form id="vbmcsettings" name="vbmcsettings" method="POST" action="/update.php" target="progressFrame"></form>');
    $('#vbmcinput').appendTo('#vbmcsettings');

});

function checkWOL() {
    if ($('#WOL').val() === 'enable')
        $('#wolcommand').val('/usr/local/emhttp/plugins/libvirtwol/scripts/wol_start');
    else {
        $('#wolcommand').val('/usr/local/emhttp/plugins/libvirtwol/scripts/wol_stop');
        $('#wolApply').prop('disabled', false);
    }

    if ("<?=$wol_running;?>" == true)
        $('#wolApply').disabled = 'disabled';
    else
        $('#wolApply').prop('disabled', false);
}

function verifyWOL() {
        $('#WOL').val( $('#WOL').val().replace(/ /g,"_") );
        $('#wolsettings').submit();
}

function checkVBMC() {
    if ($('#VBMC').val() === 'enable')
        $('#vbmccommand').val('/usr/local/emhttp/plugins/libvirtwol/scripts/vbmc_start');
    else {
        $('#vbmccommand').val('/usr/local/emhttp/plugins/libvirtwol/scripts/vbmc_stop');
        $('#vbmcApply').prop('disabled', false);
    }

    if ("<?=$vbmc_running;?>" == true)
        $('#vbmcApply').disabled = 'disabled';
    else
        $('#vbmcApply').prop('disabled', false);
}

function verifyVBMC() {
        $('#VBMC').val( $('#VBMC').val().replace(/ /g,"_") );
        $('#vbmcsettings').submit();
}
</script>