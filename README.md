Roku Screensaver
================

Create a screensaver app for Roku.

#### Requirements 
* [Roku developer kit](http://www.roku.com/developer)  - Develop Roku apps in Brightscript
* [Wordpress](http://wordpress.org) - The CMS system used to keep the photos for the screensaver
* [Redis](http://redis.io/) - To cache the api to keep DB calls minimal(optional)
* [Predis](https://github.com/nrk/predis) - An easy way to use Redis in PHP(optional)

##Instructions

#####Backend
The backend creates json API that gives the app the images it uses for the screen saver.
You can use any backend you want but I used a Wordpress site that has one picture per post.  It takes the first image in the wordpress post.

Move the roku-api.php to the root of your Wordpress website.
in the Roku source file point the `api_url` to the api. `http://example.com/roku-api.php`
