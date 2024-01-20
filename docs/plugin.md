---
title: Map plugin
nav_order: 6
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
| `layer_switcher` | (0 or 1) Show layer switcher for maps. Default setting is taken from the J-TrackGallery component; this parameter overrides the default. |
| `show_graph` | (0 or 1): show graph with elevation and speed below the map. Default is no graph (0). |
| `show_info` | (0 or 1): display track information: length, altitude, level, category, and terrain information. Default is no information (0). |
| `show_link`  | (0, 1, or 2): show link to J!TrackGallery page with track details. Use '2' to get a link that opens in a new tab. Default is no link (0). |
| `colors`     | Line color for the track segments, semi-colon separated list. Format: '#aabbcc' with aa, bb, cc the red, green, blue content in hex. Default color is 'ff00ff'. |
