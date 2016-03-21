<?php
/**
 * This view allows an employee to modify an overtime requests (may be accessed by HR admin).
 * @copyright  Copyright (c) 2014-2016 Benjamin BALET
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/bbalet/jorani
 * @since         0.2.0
 */
?>

<h2><?php echo lang('extra_view_title');?><?php echo $extra['id']; if ($name != "") {?>&nbsp;<span class="muted">(<?php echo $name; ?>)</span><?php } ?></h2>

    <label for="date" required><?php echo lang('extra_view_field_date');?></label>
    <input type="text" name="date"  value="<?php $date = new DateTime($extra['date']); echo $date->format(lang('global_date_format'));?>" readonly />
    
    <label for="duration" required><?php echo lang('extra_view_field_duration');?></label>
    <input type="text" name="duration"  value="<?php echo $extra['duration']; ?>" readonly />
    
    <label for="cause"><?php echo lang('extra_view_field_cause');?></label>
    <textarea name="cause" readonly><?php echo $extra['cause']; ?></textarea>
    
    <label for="status"><?php echo lang('extra_view_field_status');?></label>
    <select name="status" readonly>
        <option selected><?php echo lang($extra['status_name']); ?></option>
    </select><br />
    
    <br /><br />
    <?php if (($extra['status'] == 1) || ($is_hr)) { ?>
    <a href="<?php echo base_url();?>extra/edit/<?php echo $extra['id'] ?>" class="btn btn-primary"><i class="icon-pencil icon-white"></i>&nbsp;<?php echo lang('extra_view_button_edit');?></a>
    &nbsp;
    <?php } ?>
    <a href="<?php echo base_url() . $source; ?>" class="btn btn-primary"><i class="icon-arrow-left icon-white"></i>&nbsp;<?php echo lang('extra_view_button_back_list');?></a>
