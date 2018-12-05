ICON Addition/Replacement

- Go here: https://icomoon.io/app/#/select
- Create a empty set
    - If set already exists, erase it just to be safe and build a new one
- Get the file Kora3/public/assets/fonts/kora-icons/selection.json
- Import that file into set
- Add icon by dragging in SVG file (NAME IT), or edit icon to replace SVG
- Click generate fonts below
- Download font folder
- If you added a new icon
    - Open variables.scss and get the icon variable
        - Place it in Kora3/resources/assets/scss/partials/general/_variables.scss
    - Open style.scss and get the variables &:before block
        - Place it in Kora3/resources/assets/scss/partials/general/_fonts.scss
    - Build your css file
- Replace Kora3/public/assets/fonts/kora-icons/selection.json with the new selection.json file
- Replace the kora-icons* files in that same folder with the files inside the fonts folder of the same names
