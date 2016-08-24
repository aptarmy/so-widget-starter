# so-widget-starter
APT widget framework based on SiteOrigin-widget-bundle which adds responsive and prebuild-layout functionalities.

## How to use this framework ?
- First file in root directory can be leave untouched. Change file name and root directory name if necessary.
- Change widget folder name and main php file name(under the widget folder) if necessary
- It's imperative that your widget class extends `APT_Widget` class
- Make sure that you append this code `$this->get_media_query_id() => $this->get_media_query_options()` to `$form_options` array. it will add responsive functionality to your newly creative widget automatically.

## Create prebuild-layout
if you want to create prebuild-layout(builde on top of SiteOrigin widget bundle), it's very simple. Please follow these steps.
- Download prebuild layout from SiteOrigin editor in your wordpress admin page. The downloaded file extension sould be `.json`
- capture rendered screen in frontend and name it `screenshot`. The file extension can be any format like `jpg` or `png`
- Put the two files into a folder and name that folder to anything you want, `Front Page` etc., this folder name will be used to show as your prebuild-layout name