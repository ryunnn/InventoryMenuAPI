<?php

namespace ryun42680\inventorymenuapi\inventory;

use pocketmine\block\VanillaBlocks;
use ryun42680\inventorymenuapi\InventoryMenuAPI;
use pocketmine\block\inventory\BlockInventory;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\Position;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;

final class DoubleChestInventory extends InventoryBase implements BlockInventory
{

    protected Position $holder;

    public function __construct(Position $holder, protected string $title)
    {
        parent::__construct(54);

        $this->holder = Position::fromObject($holder->subtract(0, 4, 0), $holder->getWorld());
        $this->setDouble(true);
    }

    public function onOpen(Player $who): void
    {
        parent::onOpen($who);

        $network = $who->getNetworkSession();

        if (is_numeric($network->getInvManager()->getWindowId($this))) {
            $holder = $this->holder;
            $x = $holder->x;
            $y = $holder->y;
            $z = $holder->z;
            $block = VanillaBlocks::CHEST();
            $this->sendBlock($network, $block, $holder);
            $this->sendBlock($network, $block, Position::fromObject($holder->add(1, 0, 0), $holder->getWorld()));
            $nbt = CompoundTag::create()->setString('CustomName', $this->title)->setInt('pairx', ($x + 1))->setInt('pairz', $z);
            $pk = BlockActorDataPacket::create(new BlockPosition($x, $y, $z), new CacheableNbt($nbt));
            $network->sendDataPacket($pk);
            $pk = ContainerOpenPacket::blockInv(
                $network->getInvManager()->getWindowId($this),
                WindowTypes::CONTAINER,
                new BlockPosition($x, $y, $z)
            );

            InventoryMenuAPI::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($network, $pk): void {
                $network->sendDataPacket($pk);
            }), 10);
        }
    }

    public function onClose(Player $who): void
    {
        parent::onClose($who);

        $holder = $this->holder;
        $world = $holder->world;
        $network = $who->getNetworkSession();
        $pair = $holder->add(1, 0, 0);
        $this->sendBlock($network, $world->getBlock($holder), $holder);
        $this->sendBlock($network, $world->getBlock($pair), Position::fromObject($pair, $holder->getWorld()));
    }

    final public function getName(): string
    {
        return $this->title;
    }

    final public function getHolder(): Position
    {
        return $this->holder;
    }
}