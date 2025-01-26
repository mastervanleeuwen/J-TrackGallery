---
title: Overview
nav_order: 1
---
### J!TrackGallery

J!TrackGallery is a Joomla Track Gallery component, which allows to store and display GPS track data and photos.

Our Goal is to provide a non commercial, free, open source, GNU/GPL2 licensed component for Joomla 3.9, 4.x and 5.x. The code is likely to work on older versions of Joomla, but has not been tested thoroughly.

The main features are:
- Upload and edit GPS tracks (GPX tracks/routes, KML, TCX format) via frontend or backend (administrator view)
- Support for different maps: Open Street Map + variants (e.g. opencylcemap), Bing maps, and French IGN
- Basic features (distance altitude, time) for each track are calculated from the GPS track
- Add a description, category information, tags, difficulty level etc
- Add photos to each track; geotagged images are shown on the map
- Frontend provides functionality for rating and commenting
- Support for SEO/SEF URLs
- Appearance can be tweaked via css templates (partial support)

### Code and downloads

- To see the frontend, please look here for [Joomla 3](https://jtrackgallery.gta-trek.eu/) and [Joomla 5](https://jtrackgalleryj4.gta-trek.eu/)
- Documentation pages can be found [here](https://mastervanleeuwen.github.io/J-TrackGallery/)
- The code is hosted on [github](https://github.com/mastervanleeuwen/J-TrackGallery): [list of releases](https://github.com/mastervanleeuwen/J-TrackGallery/releases) and [release notes](releasenotes)
- J-TrackGallery is has also been submitted to the [Joomla extension directory](https://extensions.joomla.org/index.php?option=com_jed&view=extension&layout=default&id=15190&Itemid=145)
- For bug reports etc to this version, use the [github issues page](https://github.com/mastervanleeuwen/J-TrackGallery/issues)
- The [J!TrackGallery maps plugin](plugin) can be used to show GPS tracks in Joomla articles or other components

### Description of the packages

J!TrackGallery consists of several pieces, a _Joomla! component_, 2 _modules_ and _plugin_ packages.

The main functionality part or base package is a Joomla! *component* (`com_jtg.zip`). This is the base package, which the _modules_ and _plugin_ rely on. The J!TrackGallery component provides the interface to upload GPS files and to store information, add a description, etc and to display them.
The J!TrackGallery provides several _views_ which present the tracks in different formats. Most views can also be assigned to menu items in the menu(s) of your pages. The following views are provided:
- [Overview map](overview): this is the main entry point showing an overview of all tracks that are available on your web site
- [Track view](frontend): the main view with a map, graph, and details for a specific track - several aspects of this view can be configured to suit your needs. A button to edit the track information appears when authorised users are logged in to the site.
- _Category list_: a list of all [categories](categories)
- [Category overview](categories#category-view): a map and/or list of tracks for a specific category
- [Tag overview](categories#category-view): a map and/or list of tracks with a given Joomla tag
- _Track list_: a (sortable) list of all tracks with basic information about each track
- _User view_: a view for users that are logged in, showing a list of tracks owned, and recent comments (if enabled on the site), as well as summary statistics

The [plugin](plugin) (`plg_jtrackgallery_maps.zip`) provides the possibility to show GPS tracks on a map on other pages, for example in Joomla! articles.

Three *modules* (`mod_jtg_stats.zip`, `mod_jtg_latest.zip`, `mod_jtg_closest.zip`) provide functionality to show a list of the most popular tracks, the most recent tracks and list of tracks near a given point in a fixed location on each page.


