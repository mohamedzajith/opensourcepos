<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('giftcards/save/'.$giftcard_info->giftcard_id, array('id'=>'giftcard_form', 'class'=>'form-horizontal')); ?>
	<fieldset id="giftcard_basic_info" style="padding: 5px;">
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('giftcards_person_id'), 'name', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_input(array(
					'name'=>'person_name',
					'id'=>'person_name',
					'class'=>'form-control input-sm',
					'value'=>$selected_person_name)
					);?>
				<?php echo form_hidden('person_id', $selected_person_id);?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('giftcards_giftcard_number'), 'name', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_input(array(
					'name'=>'giftcard_number',
					'id'=>'giftcard_number',
					'class'=>'form-control input-sm',
					'value'=>$giftcard_number)
					);?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('giftcards_card_value'), 'name', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<div class="input-group input-group-sm">
					<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php echo form_input(array(
							'name'=>'value',
							'id'=>'value',
							'class'=>'form-control input-sm',
							'value'=>to_currency_no_money($giftcard_info->value))
							);?>
				</div>
			</div>
		</div>
	</fieldset>
<?php echo form_close(); ?>

<script type='text/javascript'>
//validation and submit handling
$(document).ready(function()
{
	var fill_value =  function(event, ui) {
		event.preventDefault();
		$("input[name='person_id']").val(ui.item.value);
		$("input[name='person_name']").val(ui.item.label);
	};

	var autocompleter = $("#person_name").autocomplete(
	{
		source: '<?php echo site_url("customers/suggest"); ?>',
    	minChars:0,
    	delay:15, 
       	cacheLength: 1,
		appendTo: '.modal-content',
		select: fill_value,
		focus: fill_value
    });

	// declare submitHandler as an object.. will be reused
	var submit_form = function()
	{ 
		$(this).ajaxSubmit(
		{
			success:function(response)
			{
				dialog_support.hide();
				post_giftcard_form_submit(response);
			},
			error: function(jqXHR, textStatus, errorThrown) 
			{
				post_giftcard_form_submit({message: errorThrown});
			},
			dataType:'json'
		});
	};
	
	$('#giftcard_form').validate($.extend({
		submitHandler:function(form)
		{
			submit_form.call(form)
		},
		rules:
		{
			giftcard_number:
			{
				required:true,
				number:true
			},
			value:
			{
				required:true,
				number:true
			}
   		},
		messages:
		{
			giftcard_number:
			{
				required:"<?php echo $this->lang->line('giftcards_number_required'); ?>",
				number:"<?php echo $this->lang->line('giftcards_number'); ?>"
			},
			value:
			{
				required:"<?php echo $this->lang->line('giftcards_value_required'); ?>",
				number:"<?php echo $this->lang->line('giftcards_value'); ?>"
			}
		}
	}, dialog_support.error));
});
</script>