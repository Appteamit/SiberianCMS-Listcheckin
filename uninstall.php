<?php
# listcheckin module un-installer.

$name = "listcheckin";

# Clean-up library icons
Siberian_Feature::removeIcons($name);
Siberian_Feature::removeIcons("{$name}-flat");

# Clean-up Layouts
$layout_data = array(1);
$slug = "listcheckin";

Siberian_Feature::removeLayouts($option->getId(), $slug, $layout_data);

# Clean-up Option(s)/Feature(s)
$code = "listcheckin";
Siberian_Feature::uninstallFeature($code);

# Clean-up DB be really carefull with this.
$tables = array(
    
);
Siberian_Feature::dropTables($tables);

# Clean-up module
Siberian_Feature::uninstallModule($name);