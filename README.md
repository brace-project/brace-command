# brace-mod-body

Offers `brace`-command on command line

## Example

Add this to `10_di.php`
```
$app->addModule(new CommandModule())
```

And create `30_command.php`
```php
$app->command->addCommand("command1", function() {
    echo "Hello World";
});

// Execute command1 every 5 seconds
$app->command->addInterval(5, "command1");

// Support Cron format
$app->command->addInterval("* * * * *", "command1");

// Execut once per hour
$app->command->addInterval("5 * * * *", "command1");
$app->command->addInterval("5,10,15,20 * * * *", "command1");
```

## Run the Scheduler

```bash
brace scheduler
```
