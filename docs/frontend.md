---
layout: page
title: Track view
nav_order: 2
---

# Front-end options

{: .no_toc }

<details open markdown="block">
  <summary>
    Table of contents
  </summary>
  {: .text-delta }
1. TOC
{:toc}
</details>

## Map configuration

Maps are shown via the [openlayers API](https://openlayers.org/). By default, a zoom bar and a geolocation button are shown. 
The choice of maps and the default map source can be configured in the adminstrator menu. 
All [openstreetmap variants](https://wiki.openstreetmap.org/wiki/Using_OpenStreetMap#Ready-made_online_maps) can be used, as well as Bing Maps and the maps from the French Institut Geographique National.

## Menu bar

A menu bar is shown at the top of the track view and other J!TrackGallery pages. This menu bar can be hidden by a setting in the configuration menu.

## Download buttons

To enable front-end downloads, you have to set permissions in the configuration menu in the administator view. 
Download permissions can be given to a specific user group (e.g. anyone with an account), or to everyone, by setting permissions for 'Public'.
Different formats can be offered for download:
  - Original GPX file
  - Generated GPX file
  - Generated KML file

## Uploading tracks

Track upload permissions are controlled in administrator menu ('Create' and 'Edit' permissions). 
When a user is logged in and has permission to create a track, an item 'Add track' appears in the menu bar.
The system can be configured to send a notification to an administrator user when a track is uploaded.

## Comments

A basic commenting system is available and can be enabled in the administrator menu. Permissions to post comments are controlled in the permissions menu.
E-mail notifications can be send to the owner/author of the track.

## Waypoint icons

Waypoints are displayed by icons on the map. The icon is set by the `<sym>` xml tag in the GPX file. The corresponding icon files are stored in the directory `components/com_jtg/assets/images/symbols`. Icons need to be provided in png file format, and for each icon there is a corresponding xml file that defines the anchor point of the icon (bottom left, bottom middle, etc) by specifying a relative offset in x and y.
