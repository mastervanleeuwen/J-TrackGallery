---
title: Overview
nav_order: 1
---
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
- The code is hosted on [github](https://github.com/mastervanleeuwen/J-TrackGallery): [list of releases](https://github.com/mastervanleeuwen/J-TrackGallery/releases) and [release notes](releasenotes)
- J-TrackGallery is has also been submitted to the [Joomla extension directory](https://extensions.joomla.org/index.php?option=com_jed&view=extension&layout=default&id=15190&Itemid=145)
- For bug reports etc to this version, use the [github issues page](https://github.com/mastervanleeuwen/J-TrackGallery/issues)
- The [J!TrackGallery maps plugin](plugin) can be used to show GPS tracks in Joomla articles or other components

### Description of the packages

J!TrackGallery consists of several pieces, a _Joomla! component_, 2 _modules_ and _plugin_ packages.

The main functionality part or base package is a _Joomla! component_ (`com_jtg.zip<`). This is the base package, which the _modules_ and _plugin_ rely on. The J!TrackGallery component provides the interface to upload GPS files and to store information, add a description, etc and to display them.

The [plugin](plugin) (`plg_jtrackgallery_maps.zip`) provides the possibility to show GPS tracks on a map on other pages, for example in Joomla! articles.

Two _modules_ (`mod_jtg_stats.zip` and `mod_jtg_latest.zip`) provide functionality to show a list of the most popular and most recent tracks in a fixed location on each page.


