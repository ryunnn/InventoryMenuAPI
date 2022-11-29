# InventoryMenuAPI

Easily use fake inventory in PocketMine-MP

# How to use?

* Create single chest
```php
use ryun42680\inventorymenuapi\InventoryMenuAPI;
use ryun42680\inventorymenuapi\InventoryType;

$inventory = InventoryMenuAPI::getInstance()->create(Position $holder, string $name, InventoryType::INVENTORY_TYPE_CHEST);
```

* Create double chest
```php
use ryun42680\inventorymenuapi\InventoryMenuAPI;
use ryun42680\inventorymenuapi\InventoryType;

$inventory = InventoryMenuAPI::getInstance()->create(Position $holder, string $name, InventoryType::INVENTORY_TYPE_DOUBLE_CHEST);
```

* Handle opening inventory
```php
/** @var \ryun42680\inventorymenuapi\inventory\InventoryBase $inventory */
$inventory->setOpenHandler(function (Player $player): void { });
```

* Handle closing inventory
```php
/** @var \ryun42680\inventorymenuapi\inventory\InventoryBase $inventory */
$inventory->setCloseHandler(function (Player $player): void { });
```

* Handle action
```php
/** @var \ryun42680\inventorymenuapi\inventory\InventoryBase $inventory */
$inventory->setActionHandler(function (Player $player, SlotChangeAction $action, InventoryTransactionEvent $source): void { });
```

* Set First Contents
```php
/** @var \ryun42680\inventorymenuapi\inventory\InventoryBase $inventory */
$inventory->setFirstContents(array $contents);
```

* Send inventory
```php
/** @var \ryun42680\inventorymenuapi\inventory\InventoryBase $inventory */
$inventory->send(Player $player);
```
