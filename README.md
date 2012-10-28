joomoocomments
==============

JoomooComments: joomla extension that supports comments on content articles and gallery images

 JoomooComments
================
This extension consists of a Joomla component and content plugin that
support user-generated comments on Joomla content articles and
JoomooGallery groups and images.

 Features
----------
The JoomooComments extension contains PHP and Javascript code that provides:

o  Links for users to "Like" or "Dislike" a comment and a display of
   the total "Likes" and "Dislikes" for each comment
o  Links for users to flag comments as spam and maintain a count of the total
   times users have flagged the comment
o  Links allowing users to delete their own comments and allowing advanced
   users to delete any user's comments - in the front end
o  Backend component functionality allowing site administrators to view, update,
   and delete comments
   o  Site administrators can view all comments in a list format
   o  List provides the ability to sort, filter, and edit comments
o  Backend plugin parameters that site administrators can use to control the
   appearance of the comments
   o  The first_last parameter specifies a maximum number to show
   o  The minimum_to_hide parameter specifies a minimum number to hide
o  Advanced backend plugin parameters that allow site administrators to specify
   restrictions on who can comment, supporting several ways to prevent
   and combat comment spam
   o  Site administrators can choose to allow or disallow anonymous comments
   o  Site administrators can choose to have the plugin send them an email
      when someone flags a comment as spam
   o  Plugin can optionally log IP addresses when anonymous users leave a comment
   o  Site administrators can specify whether to require users solve a
      CAPTCHA - ie. type one or two words that appear in an image - prove that
      they are human and not a spambot
   o  Plugin supports two types of CAPTCHA: OpenCaptcha and reCAPTCHA
   o  Site administrators can request or force anonymous users to enter an
      email address when they leave a comment
   o  Site administrators can request or force all users to enter a website
      when they leave a comment

The Like, Dislike, Flag as Spam, and Delete links use Ajax to communicate with
the server.  This means that these links:

o  Respond to the user's actions immediately and without a page refresh
o  Do not work when the user has Javascript disabled in their browser

 Database Columns
------------------
Following are the columns in the jos_joomoocomments table:

Field (note)     Type                   Description
--------------------------------------------------------------------------------
id               int(11) unsigned       Standard joomla primary key
created_by       int(11) unsigned       Foreign key: jos_users table
name (1)         varchar(50)            Name of user who posted comment
email (2)        varchar(150)           Email of user who posted comment
website          varchar(150)           Website of user who posted comment
ip_address       varchar(40)            IP address of user who posted comment
text             text                   Text of comment
contentid        int(11) unsigned       Foreign key: jos_content table
gallerygroupid   int(11) unsigned       Foreign key: jos_joomoogallerygroups table
galleryimageid   int(11) unsigned       Foreign key: jos_joomoogalleryimages table
created          datetime               Date and time user posted comment
published        tinyint(1) unsigned    Standard joomla published flag
likes            smallint(5) unsigned   "Like" votes for this comment
dislikes         smallint(5) unsigned   "Dislike" votes for this comment
spam             tinyint(3) unsigned    Times this comment has been flagged as spam
ordering         int(11) unsigned       Standard joomla ordering column

Notes:
(1) Backend parameters allow site administrators to request or require that
    registered and/or anonymous users specify a name when they post a comment,
    so this value does not necessarily match the name in the jos_users table
(2) Backend parameters allow site administrators to request or require that
    anonymous posters specify an email address and/or a website

 Basic Backend Parameters
--------------------------
all_articles:
    Allow comments to all articles or use placeholder for specific articles?
    Options: All articles or Use placeholder
comment_count_text:
    Short message containing comment count appended to introductory text;
        '%cc%' becomes 'xx comments'. Specify 'omit' to omit
    Text field; default value: "Full article includes %cc% comments"
ajax_or_full:
    Use ajax or full request to save and delete comments?
    Options: Use Ajax request only, Use Full request only, or Allow Either
email_on_form:
    Add email field to comment input form?  Applies to anonymous users only,
        because the database already has email address of logged-in users.
    Options: Required, Optional, or Omit
website_on_form:
    Add website field to comment input form?
    Options: Required, Optional, or Omit
first_last:
    Initially display only the specified number of first or last comments,
        and provide a link to display all
    Options: Range from initially show first 90 comments only, to initially
        show first comment only, to always show all comments, to initially
        show last comment only, to initially show last 90 comments only
minimum_to_hide:
    Minimum number of comments to hide (it seems silly to hide just one or two)
    Options: Range from hide at least 1 to hide at least 10

 Advanced Backend Parameters:
------------------------------
spam_flag_email:
    Send email (to Global Config->System->Mail from address) when spam flag for
        a comment is set?
    Options: Send or Don't Send
max_consecutive_comments:
    Maximum number of comments a user can add to an article or gallery image
        between page reloads
    Options: Range from 1 to 20 (in increments) to Unlimited
editable_name:
    Allow users to edit the name field in the form
    Options: Editable or Preset and Read-only
log_ips:
    Log IP Addresses?  Logging allows site administrators to ban irresponsible
        users (spammers) by their ip address
    Options: Always, Anonymous only, or Never
allow_anonymous:
    Allow users who aren't logged in to comment?
    Options: OK or Disallow
autopub_anonymous:
    Autopublish comments made by users who aren't logged in?
    Options: Autopublish or Publish in backend
honeypot:
    Include invisible honeypot field on form?
    Also called 'invisible captcha' this helps prevent comment spam and
        should in geneal always be set to Yes.
    Options: Yes (recommended) or No
require_captcha:
    Require poster to solve a CAPTCHA?
    Options: Always, Anonymous only, or Never
captcha_type:
    CAPTCHA Type: To use reCaptcha you must download keys from recaptcha.net -
        see components/com_joomoobase/doc/captcha.txt
    Options: OpenCaptcha or reCAPTCHA

