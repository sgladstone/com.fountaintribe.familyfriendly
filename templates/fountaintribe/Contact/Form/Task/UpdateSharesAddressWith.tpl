{*

*}

<div class="crm-block crm-form-block crm-contact-task-addtogroup-form-block">

<p>This will update home addresses so that the "Use another contact's address" checkbox is checked for:<br>
<ul>
<li>Household members</li>
<li>Heads of household</li>
</ul>
that also have a identical address that already matches the household address. If the individual has no address, or the address does not match then nothing is changed for that contact.<br><br>
<b> This action has only been tested when searching on "Household" contacts.</b>
   </p>
  
  <table class="form-layout">
      {*    <tr><td colspan=2>{ts 1=$totalSelectedContacts}Number of selected contacts to update: %1{/ts}</td></tr>  *}
          <tr><td width=75 >
        
         </td></tr>
       
         
          {if $form.notify_user_of_password}
                <tr class="crm-contact-task-createpledge-form-block-group_type">
		    <td class="label" width=255>{$form.notify_user_of_password.label}</td>
                    <td width=500>{$form.notify_user_of_password.html}</td>
                </tr>
                
         {/if}
         
          {if $form.verification_only}
                <tr class="crm-contact-task-createpledge-form-block-group_type">
		    <td class="label" width=255>{$form.verification_only.label}</td>
                    <td width=500>{$form.verification_only.html}</td>
                </tr>
         {/if}
            
         
  </table>
  
  
  <br><br>
  <table>
   <tr><td>{include file="CRM/Contact/Form/Task.tpl"}</td></tr>
   </table>
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>