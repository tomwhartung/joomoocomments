
#
# SQL to create one or more rows in the jos_joomoocomments table
#
INSERT INTO jos_joomoocomments ( created_by, name, text, contentid, created, published )
	values ( 63, "Tom Writes SQL", "text of first comment", 124, "2009-12-27 04:20:00", 1 );
INSERT INTO jos_joomoocomments ( created_by, name, text, contentid, created, published )
	values ( 63, "Tom Writes SQL", "text of second comment", 124, now(), 1 );
INSERT INTO jos_joomoocomments ( created_by, name, text, contentid, created, published )
	values ( 63, "Tom Writes SQL", "text of third comment", 124, now(), 1 );
INSERT INTO jos_joomoocomments ( created_by, name, text, contentid, created, published )
	values ( 63, "Tom Writes SQL", "text of fourth comment", 124, now(), 1 );


select * from jos_joomoocomments;
select count(*) from jos_joomoocomments;


