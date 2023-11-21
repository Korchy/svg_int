# SVG Int

Classes for working with SVG format files (.svg) on PHP.

Usage
-

Add module autoload to your .php file

    require_once('.svg_int_autoload');

Call "use" to initiate classes in your .php:

    use svg_int\SVG;

Now you can use the SVG class.

For example:

    // loading from .svg file
    $svg = new SVG('/var/tmp/test.svg');

    // get svg dimensions
    $dimensions = $svg->dimensions();

    // get 'rect' nodes
    $rects = $svg->search('rect');
    $masks = $svg->search('mask', 'id="mask-18"');

    // scale node
    $rects[0]->scale(2.0, 2.0);

    // replace nodes
    $svg->replace_node($rects[2], $rects[5]);

    // get node attribute
    $attr = $rects[0]->attribute('id');

    // set node attribute
    $rects[2]->set_attribute('my_attribute', 'my_value');

    // get transform
    $transform = $rects[0]->get_transform();

    // save to .svg file
    $svg->save('/var/tmp/new.svg');

    // return and base64
    return $svg->base64();
