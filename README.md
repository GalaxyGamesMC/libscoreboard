# libscoreboard
libscoreboard
# documents
## 1. General
```php
use galaxygames\scoreboard\Scoreboard;
...
class EgPlugin extends PluginBase{
  protected Scoreboard $scoreboard;

  public function onEnable() : void{
    $this->scoreboard = Scoreboard::getInstance();
  }
}
```

### 1.1 Create scoreboard
```php
/**
 * @var Player $player
 * @var string $objectiveName
 * @var string $displayName
 */
$this->scoreboard->create($player, $objectiveName, $displayName)
```
### 1.2 Remove scoreboard
```php
$this->scoreboard->remove($player)
```
### 1.3 Change display name
```php
/** @var string $newDisplayName */
$this->scoreboard->setDisplayName($player, $newDisplayName);
```
## 2. Lines
> **Important**
> These following instructions won't immediately show the changed line to player, they are required to send this code after the changes as the requirement to get it shown to player!<br>
```php
$this->scoreboard->update($player)
```
### 2.1 Set scoreboard line
```php
/** @var int $line Line number, range from 0 to 15*/
/** @var string $context Context of the line*/
$this->scoreboard->setLine($player, $line, $context);
```
Example:
```php
$this->scoreboard->setLine($player, 0, "line 1");
$this->scoreboard->setLine($player, 4, "line 4");
```
but the scoreboard won't have lines between 0 and 4... that is why floodLine existed!
```php
/** @var int $start The start line which it will flood, default: 0*/
/** @var int $end The end line where the flood end, default: 15*/
/** @var string $flood The line in the range will be flooded by this value, default: ""*/
$this->scoreboard->floodLine($player, $start, $end, $flood);
```
Example:
```php
$this->scoreboard->floodLine($player, 0, 3, "hello"); // Flood from line 0 to line 3 with "hello"
$this->scoreboard->floodLine($player, 4, 14); // Flood from line 4 to line 14 with empty line
```
### 2.2 Remove a line
```php
/** @var bool $brutal The removal will remove the line without putting an empty line if
this is true, default: fault */
$this->scoreboard->removeLine($player, $line, $brutal);
```
Example:
```php
$this->scoreboard->removeLine($player, 1); //Remove line 1 and leave behind an empty line
$this->scoreboard->removeLine($player, 0); //Remove line 0 without leave behind an empty line
```
### 2.3 Fluent code style
> **Note**
> All the above instructions can be written as this
```php
$this->scoreboard->create($player, "board", "My board")
    ->setLine($player, 0, "line 0")
    ->floodLine($player, 1, 12)
    ->setLine($player, 13, "line 13")
    ->removeLine($player, 9)
    ->update($player);
```
### 2.4 Make Unique
Because each lines must have a unique context, if you put a set of two lines with the same context it will fail...
To solve this, use `Scoreboard::makeUnique`

```php
$this->scoreboard->create($player, "board", "My board")
    ->setLine($player, 0, Scoreboard::makeUnique(0, "unique"))
    ->setLine($player, 1, Scoreboard::makeUnique(0, "unique"))
    ->setLine($player, 2, Scoreboard::makeUnique(0, ""))
    ->setLine($player, 3, Scoreboard::makeUnique(0, ""));
```
![image](https://github.com/GalaxyGamesMC/libscoreboard/assets/54394881/95a63a13-0af1-4b84-8341-b7b287f0be5d)
->
![image](https://github.com/GalaxyGamesMC/libscoreboard/assets/54394881/e8f1df6e-c1f2-4761-bade-f765e3fdc841)

## 3. Other functions

```php
Scoreboard::getObjectiveName(Player $player); // Return the current scoreboard's name of a player
Scoreboard::clearPlayerCache(Player $player); // This should be called when player left the server
Scoreboard::clearCache(); // Clear all data
```