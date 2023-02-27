# JsLog
An easy to use php library to show log on js console
Debug php without broking your front

# Start
JsLog is a minimalist library allowing you to put debug message along you code.
1. Import the package in your code:
```php
require('vendor/autoload.php');
use JsLog\JsLog;

// Generate an instance like this
$jslog = JsLog::getInstance();
```

2. Use it
```php
// Use it from the instance
$jslog->log('Your log');

// Or use it without instance
JsLog::getInstance()->msg('Success message !');

// Render error
$jslog->err('Error triggered...');
```
3. Custom renderer
*Jslog* allow you to render your message with css via a custom function:

```php
// Render a custom message, with customm CSS
$jslog->custom(
	"[CUSTOM DEBUG]\n", "color:orange",
	"Whouhou it worked\n", "color:red;border:1px solid red;",
	"Whouhou it worked\n", "color:darkgrey;font-weight:800;",
	"..Debug stop..\n", "color:blue;font-weight:bold"
	);
```

4. See messages
Into your web browser watch the javascript console

5. When debug are sent
Jslog will send debug only when output buffering have started. Else, message will be put in a FIFO array.
The queue of messages will be flushed at the next call of a log function or at the end of the script.