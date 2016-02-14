# WordPress ShowHide

> Show or hide content with a mouse click.

## Demo
View demo: http://akenn.org/projects/wp-showhide

## Installation
1. Unzip better-wordpress-showhide-elements.zip
2. Upload better-wordpress-showhide-elements folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

## Examples

Using an image:

```html
<img showhide="myId1" src="https://i1.ytimg.com/vi/-k5rZfw-Co0/mqdefault.jpg" />
<div id="myId1">
  <iframe width="420" height="315" src="//www.youtube.com/embed/-k5rZfw-Co0" frameborder="0" allowfullscreen></iframe>
</div>
```

Using an anchor tag:

```html
<a showhide="myId2" href="#">
  ["Hide Video", "Show Video"]
</a>
<div id="myId2">
  <iframe width="560" height="315" src="//www.youtube.com/embed/r7gmVWgEpRc" frameborder="0" allowfullscreen></iframe>
</div>
```

Using an image with content initially visible:

```html
<img showhide="myId3" showhide_visible="true" src="https://www.google.com/images/srpr/logo11w.png" />
<div id="myId3">Content goes here</div>
```

Using a div and content initially visible:

```html
<div showhide="myId4" showhide_visible="true" href="#">
  ["Hide Content", "Show Content"]
</div>
<div id="myId4">Content goes here</div>
```
