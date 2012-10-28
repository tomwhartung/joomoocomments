
README.TXT

We are not yet using these files!

I spent some time experimenting with using the joomla behavior.modal feature to
display a pop-up form.  Got the form to display but unsure how to set required
values in it.

We might have to create a formal view to get the parameters.  There is a post about
how to do this - see:
	http://www.aikidokatech.com/index.php?option=com_content&view=article&id=44:popbutton&catid=34:howto&Itemid=2

Code to call these files is in com_joomoocomments/javascript/JoomooCommentsLib.js .
Code to call that in turn is in the plugin, and that code is in turn - at this time - commented out.

The plugin has code to generate this same form, so if we decide to use this we may
want to refactor plugins/content/joomoocomments.php to use the same form.

So this works - but only kinda - and we are saving it for possible future reference.
