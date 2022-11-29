<?php

namespace ryun42680\inventorymenuapi;

use ryun42680\inventorymenuapi\inventory\ChestInventory;
use ryun42680\inventorymenuapi\inventory\DoubleChestInventory;
use ryun42680\inventorymenuapi\inventory\InventoryBase;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\Position;

final class InventoryMenuAPI extends PluginBase implements Listener
{

    use SingletonTrait;

    protected function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    protected function onLoad(): void
    {
        self::setInstance($this);
    }

    public function create(Position $holder, string $name, int $inventoryType): InventoryBase
    {
        return match ($inventoryType) {
            0 => new ChestInventory($holder, $name),
            1 => new DoubleChestInventory($holder, $name)
        };
    }

    public function onInventoryTransaction(InventoryTransactionEvent $source): void
    {
        $actions = $source->getTransaction()->getActions();

        foreach ($actions as $action) {
            if ($action instanceof SlotChangeAction) {
                $inv = $action->getInventory();

                if ($inv instanceof InventoryBase) {
                    $player = $source->getTransaction()->getSource();
                    $inv->onTransaction($player, $action, $source);
                }
            }
        }
    }
}