Detailed instructions in help/help_en.htm
Detaillierte Anweisungen siehe help/help_de.htm


***************************************************
*      C A L E N D A R     C O M M A N D S        *
*=================================================*
* Template calls:  <?php echo [command];?>        *
* Page calls:      #CMSimple $output.=[command];# *
* Page calls:      {{{PLUGIN:[command];}}}        *
*-------------------------------------------------*
* [ ] = facultative elements                      *
*  |  = or                                        *
*=================================================*
* editevents(["narrow"|"medium"|"wide"])          *
*                                                 *
* -> creates the event editing table              *
*                                                 *
* without specification editevents will take      *
* layout width as specified in plugin config file *
*-------------------------------------------------*
* calendar (["[year]","[month]","[eventpage]"])   *
*                                                 *
* -> creates the calendar                         *
*                                                 *
* unspecified year/month will result in showing   *
* present month, unspecified eventpage will take  *
* the eventpage specified in language file        *
*-------------------------------------------------*
* events(["[month]","[year]","[future month to be *
* shown]","[past month to be shown]"])            *
*                                                 *
* -> creates the list of events                   *
*                                                 *
* unspecified month/year results in present       *
* month/year, unspecified future/past month       *
* takes the specification from plugin config.     *
*-------------------------------------------------*
* nextevent()                                     *
*                                                 *
* -> creates marquee of next coming event         *
*                                                 *
***************************************************

Depending on server configuration you may have
to give writing permissions (chmod 646)

CMSimple root
+ cmsimple
+ content
+ downloads
+ images
+ plugins
  + pluginloader
  + calendar
     - admin.php
     - index.php
     + config
        - config.php     (chmod 646)
     + css
        - stylesheet.css (chmod 646)
     + content           (chmod 646)
        - *.txt          (chmod 646)
     + dp
     + help
        - help_de.htm
        - help_en.htm
     + images
     + languages
        - *.php          (chmod 646)
+ templates
