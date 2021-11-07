### J!TrackGallery

J!TrackGallery is a Joomla Track Gallery component, which allows to store and display GPS track data and photos.

Our Goal is to provide a non commercial, free, open source, GNU/GPL2 licensed component for Joomla 3.x and 4.x (with some backward compatibility for older Joomla versions). 

The main features are:
- Upload and edit GPS tracks (GPX tracks/routes, KML, TCX format) via frontend or backend (administrator view)
- Support for different maps: Open Street Map + variants (e.g. opencylcemap), Bing maps, and French IGN
- Basic features for each track are calculated from the GPS track
- Add a description, category information etc
- Add photos to each track; geotagged images are shown on the map
- Frontend provides functionality for rating and commenting
- Appearance can be tweaked via css templates (partial support)

### Code and downloads

- To see the frontend, please look here for [Joomla 3](https://jtrackgallery.gta-trek.eu/) and [Joomla 4](https://jtrackgalleryj4.gta-trek.eu/)
- Documentation pages can be found [here](https://mastervanleeuwen.github.io/J-TrackGallery/)
- The code is hosted on [github](https://github.com/mastervanleeuwen/J-TrackGallery)
- J-TrackGallery is has also been submitted to the [Joomla extension directory](https://extensions.joomla.org/index.php?option=com_jed&view=extension&layout=default&id=15190&Itemid=145)
- For bug reports etc to this version, use the [github issues page](https://github.com/mastervanleeuwen/J-TrackGallery/issues)

#### Status of the Project

0.9.22 (7 Nov 2021) Joomla 4 compatibility plus various technical improvements. The User view shows a summary of the user activity.

0.9.30 (12 Dec 2020) various technical fixes, including a fix in the install script and settings for automatic updates

0.9.29 (5 Dec 2020) photo information is now stored in the database; the main fields are the geotag information and a title field
- Image titles can be set/added in the update track forms and are shown in the gallery view as well as the popups on the map
- Geotag information is extracted from the exif in the image file; plan to add possibility to set this in the edit view in the future
several tweaks to layout and style of pages

0.9.28 (15 November 2020) is an inofficial release of J!Track Gallery; the main changes with respect to 0.9.27 are:
- Move to OpenLayers v6. This changes the appearance of maps, and makes future additions easier
- Switching between maps in the front end (user view) is not supported by the new openlayers, and has been disabled
- Support for Route information from Garmin Basecamp
- Google maps support is not present by default; the terms and conditions no longer allow to display Google Maps with the OpenLayers API. There is a workaround for this, but it has not been implemented in Joomla Track Gallery yet.

0.9.27 version of J!Track Gallery have been pushed in March 2017.  
Last months commits were mostly related to adding new feature and bug fix.

#### History of J!TrackGallery  

This is a continuation of the J!TrackGallery project that has been developed by Christophe Seguinot who also maintained a home/commmunity page at http://jtrackgallery.net which is no longer available.
J!TrackGallery may be considered as a fork of [InJooosm](http://injooosm.sourceforge.net/)
This valuable component is no longer maintained (last version is for Joomla 1.5). At the beginning of this project, I was not able to reach previous author so I decided to rename the component from InJooOSM to J!TrackGallery. 

It may be note that **InJooosm** was a fork of [joomgpstracks](http://www.joomlaos.de/Joomla_CMS_Downloads/Joomla_Komponenten/JoomGPSTracks.html)

 - Many Thanks to *Michael Pfister* (JoomGPSTracks) and *Christian Knorr* (InJooOSM) for providing this valuable component to the Open source community. 

N.B. For those who always use InJooosm 0.5.7, see manual: http://jtrackgallery.net/wiki/install/migrate-from-injoosm
