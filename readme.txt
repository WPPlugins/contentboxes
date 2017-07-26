=== Plugin Name ===
Contributors: Ralf Hortt
Donate link: http://horttcore.de/
Tags: cms, sidebar, content
Requires at least: 2.5
Tested up to: 2.5
Stable tag:	1.0

This plugin will add some awesome cms functions to your site. Add posts to static pages on the fly.

== Description ==

With this plugin you can attach each post or page other posts. When you activate the plugin a new category is added called 'Contentbox', if you put a post in this category
you can attach this post to any other page or post. The category is hidden in the loop / category list / next and previous posts so.

The plugin is development for people who use WordPress as a CMS like myself.

== Installation ==

This section describes how to install the plugin and get it working.

1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place the wp_get_contentboxes() template tag in your template (i.e. in your sidebar)

== Frequently Asked Questions ==

= Will it create any new database tables? =

No, it uses the terms and postmeta tables. If you deactivate the plugin all plugin entries will be deleted.

= I've added 2 or more contentboxes to a page but there are some contentboxes missing. What happened? =

Maybe the order number is given twice. Each order number has to be unique. This will be fixed in the next version.

= What is the name of the template tag? =
The template tag is called wp_ get _contentboxes() [without the spaces]
This template tag got 4 parameters 'before' 'after'  'link' and 'content'

__Defaults__

before = '<ul>'
after = '</ul>'
link = FALSE - the title will be linked to the post entry if it is set to TRUE.
content = TRUE - displaying the content of the post. FALSE will just display the post title

= Note =
If you enter an excerpt for this post, that will be displayed instead of the content.

== Screenshots ==


== Arbitrary section ==

== Usage ==

1. Write a new post for the 'Contentbox' category. (You can also add more categories)
1. Select a post where this post should appear.
1. Scroll down to the advanced options field. There is a new tab called 'Contentboxes'.
1. Select the contentboxes that should appear at this post
1. Enter an order number to sort the entries when you got more then 1 contentbox
1. Done!