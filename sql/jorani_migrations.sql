Alter table leaves
    add free_day enum ('monday','tuesday','wednesday','thursday','friday') default null;
Alter table leaves
    add parent_leave_id int(11) default null;
Alter table leaves
    add parent_leave bool default 0;
ALTER table leaves
    add sub_leaves_treated bool default 0;

Alter table leaves_history
    add column free_day enum ('monday','tuesday','wednesday','thursday','friday') default null;
Alter table leaves_history
    add parent_leave_id int(11) default null;
Alter table leaves_history
    add parent_leave bool default 0;
ALTER table leaves_history
    add sub_leaves_treated bool default 0;

Alter table types
    add auto_confirm bool default 0;

# reorder the columns positions to ensure structures matches with leaves table
ALTER TABLE leaves_history
    MODIFY free_day enum ('monday','tuesday','wednesday','thursday','friday') AFTER document;
ALTER TABLE leaves_history
    MODIFY parent_leave_id int(11) AFTER free_day;
ALTER TABLE leaves_history
    MODIFY parent_leave bool AFTER parent_leave_id;
ALTER TABLE leaves_history
    MODIFY sub_leaves_treated bool AFTER parent_leave;
