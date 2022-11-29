<?php

namespace ryun42680\inventorymenuapi\inventory;

use pocketmine\block\inventory\BlockInventory;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\block\BlockFactory;
use pocketmine\world\Position;
use pocketmine\player\Player;

final class ChestInventory extends InventoryBase implements BlockInventory
{

    protected Position $holder;

    public function __construct(Position $holder, protected string $title)
    {
        parent::__construct(27);

        $this->holder = Position::fromObject($holder->subtract(0, 4, 0), $holder->getWorld());
    }

    public function onOpen(Player $who): void
    {
        parent::onOpen($who);

        $network = $who->getNetworkSession();
        $holder = $this->holder;
        $x = $holder->x;
        $y = $holder->y;
        $z = $holder->z;
        $block = BlockFactory::getInstance()->get(54, 0);
        $this->sendBlock($network, $block, $holder);
        $nbt = CompoundTag::create()->setString('CustomName', $this->title);
        $pk = BlockActorDataPacket::create(new BlockPosition($x, $y, $z), new CacheableNbt($nbt));
        $network->sendDataPacket($pk);
        $pk = ContainerOpenPacket::blockInv(
            $network->getInvManager()->getWindowId($this),
            WindowTypes::CONTAINER,
            new BlockPosition($x, $y, $z)
        );
        $network->sendDataPacket($pk);
        $this->setContents($this->getFirstContents());
    }

    public function onClose(Player $who): void
    {
        parent::onClose($who);

        $holder = $this->holder;
        $this->sendBlock($who->getNetworkSession(), $holder->world->getBlock($holder), $holder);
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