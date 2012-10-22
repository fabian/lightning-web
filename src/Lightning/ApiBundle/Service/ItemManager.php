<?php

namespace Lightning\ApiBundle\Service;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;

use Lightning\ApiBundle\Entity\Item;

/**
 * Service for item access.
 *
 * @Service
 */
class ItemManager
{
    protected $listManager;

    protected $history;

    protected $doctrine;

    protected $security;

    /**
     * @InjectParams({
     *     "listManager" = @Inject("lightning.api_bundle.service.list_manager"),
     *     "history" = @Inject("lightning.api_bundle.service.history"),
     *     "doctrine" = @Inject("doctrine"),
     *     "security" = @Inject("security.context")
     * })
     */
    public function __construct($listManager, $history, $doctrine, $security)
    {
        $this->listManager = $listManager;
        $this->history = $history;
        $this->doctrine = $doctrine;
        $this->security = $security;
    }

    public function createItem($listId, $value)
    {
        $list = $this->listManager->checkList($listId);

        $item = new Item($list);
        $item->setValue($value);
        $item->setCreated(new \DateTime('now'));
        $item->setModified(new \DateTime('now'));

        $this->history->added($item);

        $em = $this->doctrine->getManager();
        $em->persist($item);
        $em->flush();

        return $item;
    }

    public function updateItem($itemId, $modified, $value, $done)
    {
        $item = $this->checkItem($itemId);

        $modified = new \DateTime($modified);
        if ($modified < $item->getModified()) {
            throw new HttpException(409, 'Conflict, list has later modification.');
        }

        // log changes
        if (!$item->getDone() && $done) {
            $this->history->completed($item);
        }
        $this->history->modified($item, $item->getValue());

        $item->setValue($value);
        $item->setDone($done);

        $em = $this->doctrine->getManager();
        $em->flush();

        return $item;
    }

    public function deleteItem($itemId)
    {
        $item = $this->checkItem($itemId);

        $this->history->deleted($item);

        $item->setDeleted(true);

        $em = $this->doctrine->getManager();
        $em->flush();
    }

    public function getItems($listId)
    {
        $list = $this->listManager->checkList($listId);

        $items = $this->doctrine
            ->getRepository('LightningApiBundle:Item')
            ->findBy(array('list' => $list->getId(), 'deleted' => false));

        return $items;
    }

    /**
     * @param string|integer $id
     */
    public function checkItem($id)
    {
        $item = $this->doctrine
            ->getRepository('LightningApiBundle:Item')
            ->find($id);

        if (!$item) {
            throw new NotFoundHttpException('No item found for id ' . $id . '.');
        }

        $list = $item->getList();

        $this->listManager->checkList($list->getId());

        return $item;
    }
}
