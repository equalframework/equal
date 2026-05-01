# Widget Types

Widgets are the building blocks of your views, allowing you to display data in various formats and styles. Understanding the different widget types and their styling capabilities is essential for creating engaging and informative user interfaces.

## Boolean

**Edit Mode vs View Mode**

**Edit Mode:**

* Render an interactive switch (toggle), which the user can change by clicking on it.
* The switch in enable or disabled based on the `readonly` property.
* An event handler is attached to the input to detect changes and update the widget's value, triggering an update event.

**View Mode:**

* Renders the same switch UI, but it is always set to `readonly` (disabled), preventing user interaction.
* No event handlers for value changes are attached.

## Date

| **PROPERTY** | **TYPE**  | **DESCRIPTION**                                                         |
| ------------ | --------- | ----------------------------------------------------------------------- |
| `heading`    | `boolean` | If `true`, emphasizes the item with larger text (only for "form" views) |

**Edit Mode vs View Mode**

**Edit Mode:**

* Renders an input field for date entry, enhanced with a jQuery date-picker for easier date selection.
* The input field is interactive, allowing users to modify the date directly within the view.
* A calendar button is provided to trigger the date-picker, enhancing usability.
* Event handlers are attached to handle value changes, date selection, and formatting.
* The input's values is formatted according to locale and usage, and updates are propagated to the widget's value.

**View Mode:**

* Renders a non-editable display of the date value.
* The date is formatted according to locale and usage, ensuring a user-friendly presentation.
* Not interactive controls or event handlers are present.

## Time

| **PROPERTY** | **TYPE**  | **DESCRIPTION**                                                         |
| ------------ | --------- | ----------------------------------------------------------------------- |
| `heading`    | `boolean` | If `true`, emphasizes the item with larger text (only for "form" views) |

**Edit Mode vs View Mode**

**Edit Mode:**

* Renders an interactive input field for entering or editing a time value.
* The input is initialized with the adapted (localized) time.
* Event handlers are attached to the input to detect changes, convert the value to UTC, and update the widget's value.
* In list layout, the input's width is adjusted for better fit.

**View Mode:**

* Renders a non-editable display of the time value.
* In list layout, the time is shown in a styled div right-aligned and formatted for readability.
* In non-list layouts, the time is displayed using a styled input view for consistency with edit mode, but it is disabled to prevent interaction.
* The value is adapted for display and a title attribute is set for tooltip.

## Datetime

| **PROPERTY** | **TYPE**  | **DESCRIPTION**                                                         |
| ------------ | --------- | ----------------------------------------------------------------------- |
| `heading`    | `boolean` | If `true`, emphasizes the item with larger text (only for "form" views) |

**Edit Mode vs View Mode**

**Edit Mode:**

* Renders an interactive input field for date and time entry, enhanced with a jQuery date-picker widget supporting both date and time selection.
* The input is initialized with the formatted value and is editable.
* A calendar button is provided to open the date/time picker popup.
* Event handlers are attached to handle input changes, date/time selection, and formatting.
* The input’s value is validated and updates are propagated to the widget’s value in ISO format.
* In list layout, the input’s width is adjusted for better fit.

**View Mode:**

* Renders a non-editable display of the date and time value.
* The value is formatted as a string according to locale and usage.
* If the field is the first column, it is rendered in a special div with the "is-first" class; otherwise, it uses a styled input view for display.
* No interactive controls or event handlers are present.

## One2Many

One2Many widgets are designed to provide a similar visual representation as a [Many2Many](#many2many) widget.

## Many2One

The widget displays a field representing a many-to-one relationship. 

| **PROPERTY** | **TYPE**  | **DESCRIPTION**                                             |
| ------------ | --------- | ----------------------------------------------------------- |
| `domain`     | `array`   | [Domain](../../../models/domains.md) conditions affecting display.                |
| `autoselect` | `boolean` | If `true`, automatically selects the only available option. |

**Edit Mode vs View Mode**

**Edit Mode:**

* Renders a composite input area inside a div.
* Shows an input field for searching/selecting related objects with autocomplete and a dropdown menu for results.
* Provides action buttons :
  * Open: Opens the selected related object in a new context.
  * Create: Opens a form to create a new related object.
  * Reset: Clears the current selection.
* The dropdown menu lists matching objects as the user types, with options for advanced search and instant creation.
* Handles keyboard navigation (arrows, enter, escape) and debounced search.

**View Mode:**

* In form layout:
  * Displays the related object's name in a styled, read-only input field.
  * Optionally shows an open button to view the related object if allowed.
* In list layout:
  * Displays the related object’s name as plain text, with optional wrapping and tooltip.
  * If allowed, clicking the value opens the related object.
  * The first column is specially styled for row-opening.
* In both modes:
  * The widget's root element is decorated with classes and with classes and data attributes for type, mode, field, and usage.

## Many2Many

The WidgetMany2Many widget is rendered as a dynamic container (a div with a minimum height) that displays a related set of records, typically in a list or table format, allowing users to manage many-to-many relationships.

**Edit Mode vs View Mode**

**Edit Mode:**

* The "add" (select) button is available, allowing users to add related records.
* The "remove" button is available, letting users remove selected records.
* Selection actions are enabled, and users can modify relationship.

**View Mode:**

* The "add" (select) button is hidden or disabled  for many2many.
* The "remove" button is not available.
* The widget is read-only; users can only view the related records.

## Integer

| **PROPERTY** | **TYPE** | **DESCRIPTION**                                               |
| ------------ | -------- | ------------------------------------------------------------- |
| `min`        | `number` | Sets the minimum allowed value for the input (HTML attribute) |
| `max`        | `number` | Sets the maximum allowed value for the input (HTML attribute) |

**Edit Mode vs View Mode**

**Edit Mode:**

* The input field's type is set to `number`, enabling native numeric input features such as increment/decrement controls and mobile numeric keyboards.
* If `min` and/or `max` are specified in the config, they are set as HTML attributes on the input.
  
**View Mode:**

* The value is displayed as non-editable text (inherited from the string widget).

## Float

**Edit Mode vs View Mode**

**Edit Mode:**

* The input field's type is set to `number`, enabling native numeric input features such as increment/decrement controls and mobile numeric keyboards.
* The input is right-aligned and allows decimal values (step="0.01").

**View Mode:**

* The value is displayed as non-editable text.
* In list layout, the value is rendered directly in the element (not in an input), right-aligned, and with no wrapping.
* Otherwise, the value is set in a disabled input field.

## Link

Link widgets render a clickable hyperlink based on the provided value and configuration. The target URL is opened in a new tab.

| **PROPERTY** | **TYPE**  | **DESCRIPTION**                                                                             |
| ------------ | --------- | ------------------------------------------------------------------------------------------- |
| `link`       | `boolean` | If `true`, the item content is displayed as a clickable link (inherited from string widget) |
| `heading`    | `boolean` | If `true`, emphasizes the item with larger text (only for "form" views)                     |

**Edit Mode vs View Mode**

**Edit Mode:**

* If the layout is "list", only the input is shown (full width)
* Otherwise, the input is shown alongside a button (with a link icon) to open the URL in a new tab.
* The input is interactive, and changes update the widget value and notify the parent layout.

**View Mode:**

* Renders a non-editable display of the link.
* In list layout, and if the value is not empty:
  * If `config.link` is an `icon`, only the open-link button is shown.
  * Otherwise, a clickable anchor tag is shown, opening the link in a new tab.
* For other layouts, a read-only input displays the value, with the open-link button beside it. 

## Signature

Signature widgets are currently in test.

**Edit Mode vs View Mode**

**Edit Mode:**

* Renders a div showing the current signature value (with line breaks).
* If no signature is present, allows the user to sign by displaying a "Sign" button.
* Clicking the "Sign" button triggers a Web eID signing process, which interacts with the user's eID card and updates the signature.

**View Mode:**

* Renders a div showing the signature value (with line breaks).
* No "Sign" button is displayed; the signature cannot be changed.

## File

File widgets are deprecated and shouldn't be used. Use binary widgets instead.

## Binary

Binary widgets are designed to handle file loads, images and signatures.

### File Handling

**Edit Mode vs View Mode**

**Edit Mode:**

* Renders a flex container with:
  * A read-only input displaying the selected file name (or empty if none).
  * A "Select" button to open the file picker
  * A hidden file input element for handling file selection.
* Clicking the input or button opens the file picker.
* When a file is selected, its metadata is stored, the value is set as based64 data URL, and the displayed file name is updated.

**View Mode:**

* Renders a div containing the text "[binary data]" to indicate a file is present, but it does not show the file name or allow interaction.

### Image Handling

You can set `usage` to `image` to display the binary data as an image and refine content type. Default is `image/jpeg`, but you can specify `image/png` or others as needed.

**Edit Mode vs View Mode**

**Edit Mode:**

* Renders a div with a background image showing the current image value.
* The div is styled as "droppable" and supports drag-and-drop for image upload.
* When an image file is dropped, it is read as a data URL and set as the new value, updating the widget.
* Visual feedback (highlight) is shown during drag-and-drop.

**View Mode:**

* Renders a div with a background image showing the current image value.
* No drag-and-drop or file selection is available; the image is display-only.

### Signature Handling

**Edit Mode vs View Mode**

**Edit Mode:**

* Renders a canvas for drawing a signature.
* Includes a "Clear" button to erase the signature.
* Uses SignaturePad library to capture and manage the signature input.
* When a signature is present, the widget automatically switches to view mode (signatures cannot be changed once set).

**View Mode:**

* Renders a div with a background image showing the saved signature (as a PNG image).
* The signature is display-only, with no editing or drawing possible.

## PDF

PDF widgets don't have specific view or edit mode behaviors.

* Both modes render the same way.
* If the layout is "list", a div displays the PDF value as plain text with a tooltip.
* For other layouts, a div contains an iframe that loads the PDF with optional height from config and a fullscreen button overlay.

| **PROPERTY** | **TYPE** | **DESCRIPTION**                                                       |
| ------------ | -------- | --------------------------------------------------------------------- |
| `height`     | `string` | Sets the height of the PDF iframe as `px` in CSS value (e.g., `500`). |

## Upload

| **PROPERTY** | **TYPE** | **DESCRIPTION**                                                        |
| ------------ | -------- | ---------------------------------------------------------------------- |
| `height`     | `string` | Sets the height of the upload area as `px` in CSS value (e.g., `150`). |

**Edit Mode vs View Mode**

**Edit Mode:**

* Renders a flex container with:
  * A hidden file input for selecting files.
  * An SVG icon representing upload.
  * A text area with a "Browse" link and instructions ("or drop the document here").
* Supports drag-and-drop for file upload, with visual highlight on drag.
* Clicking "Browse" opens the file picker.
* When a file is uploaded (via picker or drag-and-drop), its value is set and the widget triggers an update.

**View Mode:**

* Renders a div containing the text "[binary data]" to indicate a file is present, but does not show file details or allow interaction.

## Label

In Label widgets, there are no differences in rendering between view and edit mode: 

* The widget always renders a styled span displaying the value as text.
* No input, interaction, or mode-specific logic is present.
* The same layout and styling are applied regardless of mode.

## Text

| **PROPERTY** | **TYPE** | **DESCRIPTION**                                                      |
| ------------ | -------- | -------------------------------------------------------------------- |
| `height`     | `string` | Sets the height of the text area as `px` in CSS value (e.g., `105`). |

**Edit Mode vs View Mode**

**Edit Mode:**

* For "list" layout: renders a single-line input for editing text.
* For other layouts: renders a rich text editor (Quill) inside a styled div, with toolbar options for formatting, color, alignments and fullscreen.
* Handles text changes, copy-paste and updates the widget value accordingly.

**View Mode:**

* For "list" layout: displays the text as plain, non-editable content in an input view.
* For other layouts: displays the text in a styled div, converting line breads to <br> for plain text, or rendering HTML as-is for rich text.
* No editing or toolbar options are available.

## Select

Select widgets render a dropdown menu for selecting a value from a predefined list of options.

| **PROPERTY** | **TYPE**  | **DESCRIPTION**                                                         |
| ------------ | --------- | ----------------------------------------------------------------------- |
| `heading`    | `boolean` | If `true`, emphasizes the item with larger text (only for "form" views) |

**Edit Mode vs View Mode**

**Edit Mode:**

* The dropdown is interactive, allowing the user to change the value.
* Event handlers are attached for input changes and keyboard navigation.
* For list layout, the dropdown's width is adjusted for better fit.

**View Mode:**

* The value is shown as plain text, not as dropdown.
* The widget is styled according to config, but no interactive handlers are attached.

### Color Selection

When the `usage` property contains `color`, the select widget includes color indicators next to the selected value and each option in the dropdown, or in the plain text.

## String

String-based widgets (e.g., `string`, `text`, `label`) support all styling properties, allowing for rich text formatting and visual customization. This is the default widget type. 

By default, using a `string` widget provides you with a customizable String Widget. However, if `usage` contains `color`, it interprets this as a request to present a color selection widget. This creates a Select Widget with predefined color options (such as "lavender", "antiquewhite", "moccasin", etc.). For more on the Select Widget, see the [Select Widget Section](#select-widgets).

| **PROPERTY** | **TYPE**  | **DESCRIPTION**                                                         |
| ------------ | --------- | ----------------------------------------------------------------------- |
| `selection`  | `string`  | The initially selected value for the select dropdown.                   |
| `heading`    | `boolean` | If `true`, emphasizes the item with larger text (only for "form" views) |

**Edit Mode vs View Mode**

String widgets' visual representation change wether the view is in edit mode or not.

**Edit Mode:**

* Renders an input field for text entry, or a select dropdown if a selection is provided in the config.
* The input field is interactive, allowing users to modify the value directly within the view.
* Event handlers are attached to handle value changes and focus events.
* In list layout, the input's width is adjusted for better fit.

**View Mode:** 

* Renders a non-editable display of the value.
* In list layout, if the usage is `phone` or `email`, the value is rendered as clickable links (`tel:` for phone numbers and `mailto:` for emails).
* In other cases, the value is shown as plain text or using a styled input view for non-list layouts.
* Additional styling and title attributes are applied for display purposes.

### Icon Naming

String widgets allow for a flexible approach to icon representation. If the `usage` property contains the keywords `icon` or `symbol`, the widget will attempt to map the string value to a corresponding Material icon. 

| **Value Keyword** | **Description**                                                                     |
| ----------------- | ----------------------------------------------------------------------------------- |
| `success`         | Displays a green check circle icon, indicating success or completion.               |
| `info`            | Displays a blue info icon, indicating informational content.                        |
| `warn`            | Displays an orange warning icon, indicating a cautionary message.                   |
| `major`           | Displays an orangered error icon, indicating a major issue.                         |
| `important`       | Displays an orangered error icon, indicating important information.                 |
| `error`           | Displays a red report icon, indicating an error or problem.                         |
| `paid`            | Displays a green paid icon, indicating a successful payment.                        |
| `due`             | Displays a red money_off icon, indicating an outstanding payment.                   |
| `valid`           | Displays a green check circle icon, indicating validity.                            |
| `invalid`         | Displays a red error icon, indicating invalidity.                                   |
| `for`             | Displays a green thumb_up icon, indicating support or agreement.                    |
| `against`         | Displays a red thumb_down icon, indicating opposition or disagreement.              |
| `abstain`         | Displays a grey remove icon, indicating abstention.                                 |
| `folder`          | Displays a folder icon with a specific color, indicating a directory or collection. |
| `file`            | Displays a grey description icon, indicating a file.                                |
| `document`        | Displays a grey description icon, indicating a document.                            |
| `full`            | Displays a green circle icon, indicating completeness.                              |
| `part`            | Displays an orange adjust icon, indicating partial completion.                      |
| `none`            | Displays a red radio_button_unchecked icon, indicating absence or null value.       |

If the value does not match any key in the mapping, it simply displays the value as a Material icon.

**Example:** If the value of the field is "success", the widget will render a green check circle icon. If the value is "folder", it will render a folder icon with a specific color. If the value is "unknown", it will attempt to render an icon named "unknown" from the Material icons set, and if that doesn't exist, it will display the text "unknown".

```json
{
  "type": "string",
  "usage": "icon",
  "field": "status",
  "label": "Status"
}
```

## Spacing Values

Spacing properties accept CSS syntax:

* Single value: `8px` (all sides)
* Horizontal/Vertical: `10px 5px` (top/bottom, left/right)
* Four sides: `10px 5px 10px 5px` (top, right, bottom, left)
* Relative units: `0.5rem`, `1em`
* 