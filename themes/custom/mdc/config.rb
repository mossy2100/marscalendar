http_path = "/themes/custom/mdc"
css_dir = "css"
sass_dir = "sass"
images_dir = "images"
javascripts_dir = "js"
fonts_dir = "bootstrap/assets/fonts/bootstrap"
generated_images_dir = "images"
http_images_path = http_path + "/" + generated_images_dir
http_generated_images_path = http_images_path
output_style = (environment == :production) ? :compressed : :expanded
require 'compass'
