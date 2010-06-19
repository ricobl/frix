# TODO 

Things to improve and ideas...

* find-out how to get the `App` from a `Model`;
* create and use a signals system;
* allow uploaded file auto renaming;
* automatic creation of media directories when uploading;
* Image: allow default conversion of image types (eg: BMP to GIF);

## AdminOptions

* registry (each app's `admin.php` file):
	* Model proxying;
	* default queryset method;
	* custom views;
	* custom permissions;
 * registro (em cada app: arquivo 'admin.php')
	* urls based on `AdminOption` sub-class instead of `Model` sub-class;

## Templates

When extending templates, the engine loads (using PHP's include) the
base template in the `extend` function. Maybe this inclusion could be
delayed to an `end_extend` function.

The child blocks would be kept in an auxiliary array and every time a
block is opened in the parent, a check could be made before rendering
the content with the code on the parent.

