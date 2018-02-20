{* template block that contains the new field *}
<table id="crm-group-form-block-custom-Teams">
    <tr>
        <td class="label">{$form.teams.label}</td>
        <td>{$form.teams.html}</td>
    </tr>
</table>
{* reposition the above block after #someOtherBlock *}
<script type="text/javascript">
    cj('#crm-group-form-block-custom-Teams').insertAfter('.custom-group-CCA_Group_Settings table');
</script>