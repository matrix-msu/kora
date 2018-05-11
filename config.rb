Encoding.default_external = "utf-8" 
# ^ this ^ is here because it prevents the following error:
# Encoding::InvalidByteSequenceError on line ["22"] of /usr/lib/ruby/vendor_ruby/execjs/encoding.rb: "\xE6" on US-ASCII
require 'compass/import-once/activate'
require 'autoprefixer-rails'
# Require any additional compass plugins here.

# Set this to the root of your project when deployed:
http_path = '/'
css_dir = 'public/assets/css'
sass_dir = 'resources/assets/scss'
images_dir = 'public/assets/images'
javascripts_dir = 'public/assets/javascripts'
fonts_dir = 'fonts'
output_style = :compressed

on_stylesheet_saved do |file|
  css = File.read(file)
  map = file + '.map'

  if File.exists? map
    result = AutoprefixerRails.process(css,
      from: file,
      to:   file,
    map:  { prev: File.read(map), inline: false })
    File.open(file, 'w') { |io| io << result.css }
    File.open(map,  'w') { |io| io << result.map }
  else
    File.open(file, 'w') { |io| io << AutoprefixerRails.process(css) }
  end
end
# You can select your preferred output style here (can be overridden via the command line):
# output_style = :expanded or :nested or :compact or :compressed

# To enable relative paths to assets via compass helper functions. Uncomment:
# relative_assets = true

# To disable debugging comments that display the original location of your selectors. Uncomment:
# line_comments = false


# If you prefer the indented syntax, you might want to regenerate this
# project again passing --syntax sass, or you can uncomment this:
# preferred_syntax = :sass
# and then run:
# sass-convert -R --from scss --to sass sass scss && rm -rf sass && mv scss sass
