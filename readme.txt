[center]
[b][color=red][size=18pt]SA Chat Bar[/size][/color][/b]
[color=blue]MySpace/Facebook style chat bar for Simple Machines Forum.[/color]

[hr]
[size=10pt][b][color=green][If you like this mod please donate by clicking here.](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4ECXFSWZSM89U)[/color][/b][/size]
[/center]
[hr]

SA Chat bar software integrates with SMF and works with SMF without working within SMF. This is done by loading the SMF cookie from the browser and verifying it against data saved in the database. This way of working allows SA Chat to run inside and outside of SMF and throughout your website as long as the user is logged into SMF.

The integration codes can be put into any html code. One in the head and one in the body. The install script however installs the JavaScript codes by default in the default theme, but you can add these codes into whatever template you like SMF or not.

Just insert this in the head...
[code]<script type="text/javascript" src="http://www.your-site.com/path_to_forum/sachat/index.php?action=head"></script>[/code]

And this in the body...
[code]<script type="text/javascript" src="http://www.your-site.com/path_to_forum/sachat/index.php?action=body"></script>[/code]

Custom themes can be called in the body code also by adding the theme into the url like so.
[code]<script type="text/javascript" src="http://www.your-site.com/path_to_forum/sachat/index.php?action=body&theme=dark"></script>[/code]

There is so far a quite bit of possibilities left for you to explore with the custom template and chat sound system SA Chat has to offer. I am looking forward to what theme designers might have to add to this equation.

You can also customize how SA Chat works from the SMF Admin Panel. You can set the chat heart beat, message window heart beat and set how many days until the chat purges old messages.

SA Chat also comes with share links to MySpace, Facebook and Twitter. This allows your site visitors to post the page they are at to any of these social networks.

In the Who's Online section of the chat you will see all of your buddies. Just click on a buddy to start a chat session.

It is also possible to chat to people that are not in your buddy list. This mod however does not edit any of the profile templates or display template in order to achieve this. The edit is however simple and all a SMF theme developer or webmaster has to remember is this piece of code below.

This code will open a chat window to chat to user 4. User 4 is who ever has the SMF user id 4.
[code]<a onclick="javascript:chatTo('4');return false;" href="javascript:void(0)">User 4</a>[/code]

So if I wanted to chat to user id 5
[code]<a onclick="javascript:chatTo('5');return false;" href="javascript:void(0)">User 5</a>[/code]

[color=red][size=14pt][b]License[/b][/size][/color]
You are allowed to use and modify SA Chat on your website, however you are not allowed to distribute SA Chat without permission. Any user who offers download of SA Chat without permission will have proper actions taken against them. This includes a take down notice to their host and maybe legal actions. Currently there are two places you may be able to get SA Chat the SMF Site(http://www.simplemachines.org) and the SA Mod Site(http://sa-mods.info). If you downloaded this file from any other source besides the sources mention please report the site you downloaded this mod at http://sa-mods.info.

THIS PACKAGE IS PROVIDED "AS IS" AND WITHOUT ANY WARRANTY. ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHORS BE LIABLE TO ANY PARTY FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES ARISING IN ANY WAY OUT OF THE USE OR MISUSE OF THIS PACKAGE.

As of revision 10 makes use of jQuery JavaScript Library v1.4.2 - http://jquery.com/, Copyright 2010, John Resig. Dual licensed under the MIT or GPL Version 2 licenses - http://jquery.org/license. Which also includes Sizzle.js - http://sizzlejs.com/, Copyright 2010, The Dojo Foundation. Released under the MIT, BSD, and GPL Licenses.

As of revision 16 makes use of jQuery viewportOffset v0.3 - http://benalman.com/projects/jquery-misc-plugins/, Copyright 2010 "Cowboy" Ben Alman. Dual licensed under the MIT and GPL licenses - http://benalman.com/about/license/.

As of revision 17 makes use of jQuery Translate plugin v1.3.2 - http://code.google.com/p/jquery-translate/, Copyright 2009 Balazs Endresz. Dual licensed under the MIT and GPL licenses.

SA Chat default theme images courtesy of BittBox - http://www.bittbox.com/category/freebies/

All other code is copyright 2010 sa-mods.info

[color=red][size=14pt][b]Special Thanks[/b][/size][/color]
Al Capwn (beta testing)
nend (origanal author)

[color=red][size=14pt][b]Before you Install, Please make sure to backup any changes to themes you have made and uninstall any prior versions of SA Chat.[/b][/size][/color]