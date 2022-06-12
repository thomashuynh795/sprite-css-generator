#!/usr/bin/php
<?php
function no_recursive_scandir($directory_name) {
    $pointer = opendir($directory_name);
    $array1 = [];
    if($pointer) {
        while(false !== ($entry = readdir($pointer))) {
            if(($entry != "." && $entry != "..") && (substr($entry, -4, 4) == ".png")) {
                array_push($array1, $directory_name."/".$entry);
            }
        }
    }
    closedir($pointer);
    return $array1;
}
//-----------------------------------------------------------------------------------------------------------
function recursive_scandir($directory_name, &$tab_files_paths) {
    if(is_dir($directory_name) && $pointer = opendir($directory_name)) {
        while(($entry = readdir($pointer)) !== false) {
            if($entry != '.' && $entry != '..') {
                if(preg_match('#.png#', $entry)) $tab_files_paths[] = $directory_name.'/'.$entry;
                recursive_scandir($directory_name.'/'.$entry, $tab_files_paths);
            }
        }
        closedir($pointer);
    }
}
//-----------------------------------------------------------------------------------------------------------
function get_size($tab_files_paths) {
    $array = [];
    for($i = 0; $i < count($tab_files_paths); $i++) {
        $size1 = getimagesize($tab_files_paths[$i]);
        array_push($array, $size1[0]);
        array_push($array, $size1[1]);
    }
    return $array;
}
//-----------------------------------------------------------------------------------------------------------
function find_x_sprite_size($tab_files_dimensions) {
    $tab_elements_count = count($tab_files_dimensions);
    $sprite_x = $tab_files_dimensions[0];
    for($i = 0; $i < $tab_elements_count; $i++) {
        if($sprite_x < $tab_files_dimensions[$i] && $i%2 == 0) $sprite_x = $tab_files_dimensions[$i];
    }
    return $sprite_x;
}
//-----------------------------------------------------------------------------------------------------------
function find_y_sprite_size($tab_files_dimensions, $padding_value) {
    $tab_elements_count = count($tab_files_dimensions);
    $sprite_y = 0;
    for($i = 1; $i < $tab_elements_count; $i++) {
        if($i%2 == 1) $sprite_y += $tab_files_dimensions[$i];
    }
    $tab_elements_count /= 2;
    $tab_elements_count--;
    $sprite_y += $tab_elements_count*$padding_value;
    return $sprite_y;
}
//-----------------------------------------------------------------------------------------------------------
function create_sprite($sprite_x, $sprite_y) {
    $image = imagecreatetruecolor($sprite_x, $sprite_y);
    $background = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $background);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    return $image;
}
//-----------------------------------------------------------------------------------------------------------
function copy_image_to_sprite($tab_files_paths, $tab_files_dimensions, $sprite, $padding_value) {
    $i = 0;
    $distance = 0;
    $array_x = [];
    $array_y = [];
    $size1 = count($tab_files_dimensions);
    for($j = 0; $j < $size1; $j++) {
        if($j%2 == 0) array_push($array_x, $tab_files_dimensions[$j]);
    }
    for($j = 0; $j < $size1; $j++) {
        if($j%2 == 1) array_push($array_y, $tab_files_dimensions[$j]);
    }
    foreach($tab_files_paths as $value) {
        $tmp = imagecreatefromstring(file_get_contents($value));
        imagecopy($sprite, $tmp, 0, $distance, 0, 0, $array_x[$i], $array_y[$i]);
        $distance += $array_y[$i] + $padding_value;
        $i++;
    }
}
//-----------------------------------------------------------------------------------------------------------
function generate_full_images_informations_array($tab_files_paths, $tab_files_dimensions) {
    $tab_all_files_infos = [];
    $size1 = count($tab_files_paths) + count($tab_files_dimensions);
    $i = 0;
    $j = 0;
    while($size1 != 0) {
        array_push($tab_all_files_infos, $tab_files_paths[$i]);
        $i++;
        array_push($tab_all_files_infos, $tab_files_dimensions[$j]);
        $j++;
        array_push($tab_all_files_infos, $tab_files_dimensions[$j]);
        $j++;
        $size1 -= 3;
    }
    return $tab_all_files_infos;
}
//-----------------------------------------------------------------------------------------------------------
function generate_html($tab_files_paths, $css_file_name) {
    $html_file_name = "index.html";
    $content = "<!DOCTYPE html>\n";
    $content .= "<html lang=\"en\">\n";
    $content .= "<head>\n";
    $content .= "\t<link rel=\"stylesheet\" href=\"$css_file_name\">\n";
    $content .= "\t<title>Document</title>\n";
    $content .= "</head>\n";
    $content .= "<body>\n";
    $tab_elements_count = count($tab_files_paths);
    for($i = 0; $i < $tab_elements_count; $i++) {
        $string = rtrim($tab_files_paths[$i], ".png");
        $string = "sprite-".str_replace("/", "-", $string);
        $string = str_replace("--", "-", $string);
        $content .= "\t<p class=\"sprite $string\"></p>\n";
    }
    $content .= "<body>\n\n";
    file_put_contents($html_file_name, $content);
}
//-----------------------------------------------------------------------------------------------------------
function generate_css($tab_all_files_infos, $png_file_name, $padding_value, $css_file_name) {
    $position = 0;
    $content = ".sprite {\n";
    $content .= "\tbackground-image: url($png_file_name);\n";
    $content .= "\tbackground-repeat: no-repeat;\n";
    $content .= "\tdisplay: block;"."\n}\n\n";
    $tab_elements_count = count($tab_all_files_infos);
    for($i = 0; $i < $tab_elements_count; $i++) {
        $string = rtrim($tab_all_files_infos[$i], ".png")." {\n";
        $string = ".sprite-".str_replace("/", "-", $string);
        $string = str_replace("--", "-", $string);
        $content .= $string;
        $content .= "\tbackground-position: 0px ".-$position."px;\n";
        $content .= "\twidth: ".$tab_all_files_infos[$i + 1]."px;\n";
        $content .= "\theight: ".$tab_all_files_infos[$i + 2]."px;\n}\n\n";
        $i += 2;
        $position += $tab_all_files_infos[$i] + $padding_value;
    }
    file_put_contents($css_file_name, $content);
}
//------------------------------------------------------------------------------------
function error() {
    system("clear");
    $content = "\t\t\t\t\t\t\t\tMANUAL\n\n";
    $content .= "NAME\n";
    $content .= "\tcss_generator.php - concatenate png files of a directory in a sprite and generate a css of the sprite\n\n";
    $content .= "SYNOPSIS\n";
    $content .= "\tcss_generator.php [OPTION]... [DIRECTORY]...\n\n";
    $content .= "DESCRIPTION\n";
    $content .= "\tconcatenate png files of a directory in a sprite and generate a css of the sprite\n\n";
    $content .= "\t-i, --output-image\n";
    $content .= "\t\t[NAME OF SPRITE]...\n\n";
    $content .= "\t-s, --output-style\n";
    $content .= "\t\t[NAME OF GENERATED CSS]...\n\n";
    $content .= "\t-p, --padding\n";
    $content .= "\t\t[NUMBER OF PIXELS BETWEEN EACH IMAGE]...\n\n";
    $content .= "press ctrl + c\n\n";
    echo $content;
    sleep(3600);
    system("clear");
    exit;
}
//-----------------------------------------------------------------------------------------------------------
function is_padding_integer($padding_value) {
    $padding_value = intval($padding_value);
    if(!$padding_value || $padding_value < 0) error();
}
//-----------------------------------------------------------------------------------------------------------
function update_arguments($argv, &$png_file_name, &$css_file_name, &$padding_value) {
    $tab_elements_count = count($argv);
    $directory_path = $argv[$tab_elements_count - 1];
    foreach($argv as $key => $value) {
        if($value == "-i" || $value == "--output-image") {
            $png_file_name = $argv[$key + 1];
            if($png_file_name[0] == "-" || $png_file_name == $directory_path) error();
            $png_file_name = $argv[$key + 1].".png";
        }
    }
    foreach($argv as $key => $value) {
        if($value == "-s" || $value == "--output-style") {
            $css_file_name = $argv[$key + 1];
            if($css_file_name[0] == "-" || $css_file_name == $directory_path) error();
            $css_file_name = $argv[$key + 1].".css";
        }
    }
    foreach($argv as $key => $value) {
        if($value == "-p" || $value == "--padding") {
            $padding_value = $argv[$key + 1];
            is_padding_integer($padding_value);
            if($padding_value == $directory_path) error();
        }
    }
}
//-----------------------------------------------------------------------------------------------------------
system("clear");
echo "please wait...";
$var = count($argv);
if(!is_dir($argv[$var - 1])) error();
$directory_name = $argv[$var - 1];
$padding_value = 0;
$png_file_name = "sprite.png";
$css_file_name = "style.css";
$tab_files_paths = [];
update_arguments($argv, $png_file_name, $css_file_name, $padding_value);
if(((array_search("-r", $argv)) || (array_search("--recursive", $argv))))
    recursive_scandir($directory_name, $tab_files_paths);
else $tab_files_paths = no_recursive_scandir($directory_name);
if(!$tab_files_paths) {
        echo "no png file in the directory\n\npress ctrl + c\n\n";
        sleep(3600);
        system("clear");
        exit;
}
$tab_files_dimensions = get_size($tab_files_paths);
$sprite_x = find_x_sprite_size($tab_files_dimensions);
$sprite_y = find_y_sprite_size($tab_files_dimensions, $padding_value);
$sprite = create_sprite($sprite_x, $sprite_y);
copy_image_to_sprite($tab_files_paths, $tab_files_dimensions, $sprite, $padding_value);
imagepng($sprite, $png_file_name, 0, -1);
system("clear");
$tab_all_files_infos = generate_full_images_informations_array($tab_files_paths, $tab_files_dimensions);
generate_css($tab_all_files_infos, $png_file_name, $padding_value, $css_file_name);
generate_html($tab_files_paths, $css_file_name);
exit;