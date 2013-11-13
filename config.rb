 
 project_path = __dir__
 
 css_dir = "css"
 images_dir = "images"
 sass_dir = "scss"
 javascripts_dir = "js"
 fonts_dir = "fonts"
 
 printf("current environment: %s\n", environment)
 
 output_style = (environment == :production) ? :compressed : :expanded