---
title: Map plugin
nav_order: 5
---
### J!TrackGallery maps plugin

The J!TrackGallery maps plugin provides a simple way to show GPS tracks in Joomla articles or in other Joomla components. The basic syntax to show a track is to insert the following in your page:

```
{JTRACKGALLERYMAP} gpxfilename=sample_trek_valroc_1.gpx, show_link=1 {/JTRACKGALLERYMAP}
```
the track to be shown is specified by filename (gpxfilename=) or by numerical track id (id=). The file name used here is one the example tracks.

The plugin accepts the following optional parameters (comma-separated):

| `map_width`  | Width of map (default is set in plugin options) |
| `map_height` | Height of map (default is set in plugin options) |
| `show_link`  | (0, 1, or 2): show link to J!TrackGallery page with track details. Use '2' to get a link that opens in a new tab. Default is no link (0). |
