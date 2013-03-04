<?php
/*
 * Xibo - Digitial Signage - http://www.xibo.org.uk
 * Copyright (C) 2006-2013 Daniel Garner
 *
 * This file is part of Xibo.
 *
 * Xibo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version. 
 *
 * Xibo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Xibo.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * Theme variables:
 *  form_id = The ID of the Form
 * 	form_action = The URL for calling the Layout Edit Transaction
 * 	form_meta = Additional META information required by Xibo in the form submit call
 * 	
 */
defined('XIBO') or die("Sorry, you are not allowed to directly access this page.<br /> Please press the back button in your browser.");
?>
<form id="<?php echo Theme::Get('form_id'); ?>" class="XiboForm" method="post" action="<?php echo Theme::Get('form_action'); ?>">
	<?php echo Theme::Get('form_meta'); ?>
	<table>
        <tr>
            <td><label for="username" title="<?php echo Theme::Translate('The Login Name of the user.'); ?>"><?php echo Theme::Translate('Username'); ?></label></td>
            <td><input type="text" id="" name="username" value="<?php echo Theme::Get('username'); ?>" class="required" /></td>
        </tr>
        <tr>
            <td><label for="email" title="<?php echo Theme::Translate('The Email for this user.'); ?>"><?php echo Theme::Translate('Email'); ?></label></td>
            <td><input type="text" id="email" name="email" value="<?php echo Theme::Get('email'); ?>" class="email" /></td>
        </tr>
        <tr>
        	<td><label for="homepage" title="<?php echo Theme::Translate('The users Homepage. This should not be changed until you want to reset their homepage.'); ?>"><?php echo Theme::Translate('Homepage'); ?></label></td>
        	<td><?php echo Theme::SelectList('homepage', Theme::Get('homepage_field_list'), 'homepageid', 'homepage', Theme::Get('homepage')); ?></td>
   		</tr>
        <tr>
            <td><label for="usertypeid" title="<?php echo Theme::Translate('What is this users type?'); ?>"><?php echo Theme::Translate('User Type'); ?></label></td>
            <td><?php echo Theme::SelectList('usertypeid', Theme::Get('usertype_field_list'), 'usertypeid', 'usertype', Theme::Get('usertypeid')); ?></td>
		</tr>
        <tr>
            <td><label for="retired" title="<?php echo Theme::Translate('Is this user retired?'); ?>"><?php echo Theme::Translate('Retired'); ?></label></td>
            <td><input type="checkbox" name="retired" <?php echo Theme::Get('retired_option_checked'); ?> /></td>
        </tr>
    </table>
</form>
