# Copilot Instructions for AI Agents

## Project Overview
This is a static, single-page web application based on the Start Bootstrap Grayscale theme. The project consists of HTML, CSS, and JavaScript files, with assets for images and icons. It is intended to be served as a simple website, likely for demo or portfolio purposes.

## Key Files and Structure
- `index.html`: Main entry point. Contains all page sections (navigation, masthead, about, projects, contact) and links to CSS/JS.
- `css/styles.css`: Custom and Bootstrap-based styles. Uses CSS variables for theme colors. Large file, mostly theme and Bootstrap overrides.
- `js/scripts.js`: Handles navbar shrinking, scrollspy activation, and responsive navbar toggling. Uses Bootstrap JS APIs.
- `assets/img/`: Contains images referenced in the HTML and CSS.

## Patterns and Conventions
- **Bootstrap Integration**: Uses Bootstrap 5 for layout and components. Custom styles extend Bootstrap via CSS variables in `styles.css`.
- **Font Awesome**: Icons loaded via CDN in `index.html`.
- **Google Fonts**: Varela Round and Nunito loaded via CDN.
- **Responsive Navbar**: JavaScript in `scripts.js` manages navbar shrinking and toggling for mobile.
- **Scrollspy**: Enabled via Bootstrap's JS API for navigation highlighting.
- **No Build Step**: All files are static. No npm, package.json, or build tools detected.
- **No Tests**: No test framework or test files present.
- **No External API Calls**: All content and assets are local or loaded via CDN.

## Developer Workflow
- **Local Development**: Edit HTML, CSS, and JS directly. Refresh browser to see changes.
- **Debugging**: Use browser dev tools for inspecting layout, styles, and JS behavior.
- **Adding Assets**: Place new images in `assets/img/` and reference them in HTML/CSS.
- **Customizing Theme**: Modify CSS variables in `styles.css` for color changes.

## Examples
- To add a new section, edit `index.html` and update navigation links as needed.
- To change the primary color, update `--bs-primary` in `css/styles.css`.
- To add a new navbar item, update both the HTML and JS if scrollspy or toggling is affected.

## Integration Points
- **Bootstrap**: All layout and interactive components rely on Bootstrap 5 (via CDN).
- **Font Awesome**: Icons are used in navigation and buttons.

## Important Notes
- Do not add build tools or package managers unless explicitly requested.
- Maintain CDN links for Bootstrap, Font Awesome, and Google Fonts in `index.html`.
- Keep all assets in the `assets/` directory.
- Follow the existing HTML structure and class naming for consistency.

---
_If any conventions or workflows are unclear, please ask for clarification or provide examples from the codebase._
