ALTER TABLE `cw_time_sheet_time_line` ADD `approval_status` INT NULL DEFAULT '1' AFTER `credit`;


ALTER TABLE `cw_time_sheet_time_line` ADD `other_work_name` VARCHAR(255) NULL DEFAULT '0' AFTER `approval_status`;

ALTER TABLE `cw_time_sheet_time_line` ADD `team` VARCHAR(255) NULL DEFAULT '0' AFTER `other_work_name`;


ALTER TABLE `cw_tonnage_approval` ADD `team` VARCHAR(255) NULL DEFAULT '0' AFTER `approval_status`;
