---
title: map plugin
nav_order: 3
---
### J!TrackGallery maps plugin

The J!TrackGallery maps plugin provides a simple way to show GPS tracks in Joomla articles or in other Joomla components. The basic syntax to show a track is to insert the following in your page:

```
{JTRACKGALLERYMAP} gpxfilename=sample_trek_valroc_1.gpx {/JTRACKGALLERYMAP}
```
the track to be shown is specified by filename (gpxfilename=) or by numerical track id (id=). The file name used here is one the example tracks.

The plugin accepts the following optional parameters:

| `map_width`  | Width of map (default is set in plugin options) |
| `map_height` | Height of map (default is set in plugin otptions) |
| `show_link`  | (true or false): show link to J!TrackGallery page with track details |
