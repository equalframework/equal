## Overview

### How frontend libraries work together

We have two main libraries that are used in our frontend applications: `eQualUI` and `sb-shared-lib` which is provided by [`symbiose-ui` repository](https://github.com/yesbabylon/symbiose-ui/tree/dev-2.0). 

<center><img src="/_assets/uml/front_end_libs.png" /></center>

If you wish to use `sb-shared-lib` in your app, you can find more details in [this how-to](../../how-tos-references/using-shared-lib.md).

!!! notes "Overriding apps"
    It is possible to override an App by using the same ID (will be put as-is in the `/public` folder: if the target folder already exists, it is overwritten)


When a custom app extending another app is defined, the related URL is generated using the logic : `/app/#/:package/:app_id`


---