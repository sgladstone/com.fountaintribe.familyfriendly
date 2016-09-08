{*

*}
<div class="crm-block crm-form-block crm-contact-task-addtogroup-form-block">

<p>This will update existing relationships so that the permissions on those relations are as follows:<br>
<ul>
 <li>Spouses and partners will get 2-way permission to each other
 <li>Spouses and partners will get 2-way permission to the household
 <li>Parents will get permission to their children in this household
 <li>Households will get permission to all household members and heads of households
 <li>Parents of children in this household will get 2-way permission to the household
 <li>Heads of household will get permission to this household
 </ul>
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