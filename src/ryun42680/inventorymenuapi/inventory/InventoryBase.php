<?php

namespace ryun42680\inventorymenuapi\inventory;

use ryun42680\challengeplugin\ChallengePlugin;
use pocketmine\block\Block;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\SimpleInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Utils;
use pocketmine\world\Position;
use ryun42680\inventorymenuapi\InventoryMenuAPI;

abstract class InventoryBase extends SimpleInventory
{

    private ?\Closure $openHandler = null;
    private ?\Closure $closeHandler = null;
    private ?\Closure $actionHandler = null;

    protected array $firstContents = [];
    private bool $isDouble = false;

    final protected function setDouble(bool $isDouble): void
    {
        $this->isDouble = $isDouble;
    }

    final public function setOpenHandler(\Closure $closure): self
    {
        Utils::validateCallableSignature(function (Player $player): void {
        }, $closure);
        $this->openHandler = $closure;
        return $this;
    }

    final public function setCloseHandler(\Closure $closure): self
    {
        Utils::validateCallableSignature(function (Player $player): void {
        }, $closure);
        $this->closeHandler = $closure;
        return $this;
    }

    final public function setActionHandler(\Closure $closure): self
    {
        Utils::validateCallableSignature(function (Player $player, SlotChangeAction $action, InventoryTransactionEvent $source): void {
        }, $closure);
        $this->actionHandler = $closure;
        return $this;
    }

    final public function getFirstContents(): array
    {
        return $this->firstContents;
    }

    final public function setFirstContents(array $contents): self
    {
        $this->firstContents = $contents;
        return $this;
    }

    public function onTransaction(Player $player, SlotChangeAction $action, InventoryTransactionEvent $source): void
    {
        if (!is_null($this->actionHandler)) {
            ($this->actionHandler)($player, $action, $source);
        }

        if ($source->isCancelled()) {
            $player->getNetworkSession()->getInvManager()->syncSlot($player->getCursorInventory(), 0);
        }
    }

    final public function send(Player $player): void
    {
        $player->setCurrentWindow($this);
        $contents = $this->firstContents;

        if (!is_null($this->openHandler)) {
            ($this->openHandler)($player);
        }

        if ($this->isDouble) {
            InventoryMenuAPI::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($contents): void {
                $this->setContents($contents);
            }), 13);
        } else {
            $this->setContents($contents);
        }
    }

    public function onClose(Player $who): void
    {
        parent::onClose($who);

        if (!is_null($this->closeHandler)) {
            ($this->closeHandler)($who);
        }
    }

    final protected function sendBlock(NetworkSession $network, Block $block, Position $holder): void
    {
        $pk = UpdateBlockPacket::create(
            new BlockPosition($holder->x, $holder->y, $holder->z),
            RuntimeBlockMapping::getInstance()->toRuntimeId($block->getStateId()),
            UpdateBlockPacket::FLAG_NETWORK,
            UpdateBlockPacket::DATA_LAYER_NORMAL);
        $network->sendDataPacket($pk);
    }
}