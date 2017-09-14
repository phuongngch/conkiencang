<?php
/**
 * Author: Nhan Nguyen
 * Date: 30/11/2016
 */

namespace Gos\Quickar\Controller\Finder;

class CarYear extends \Magento\Framework\App\Action\Action
{
    protected $layoutFactory;
    protected $request;
    protected $productFactory;

    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Framework\View\LayoutFactory $layoutFactory,
                                \Magento\Framework\App\Request\Http $request,
                                \Magento\Catalog\Model\ProductFactory $productFactory)
    {
        $this->request = $request;
        $this->layoutFactory = $layoutFactory;
        $this->productFactory = $productFactory;
        return parent::__construct($context);
    }

    /**
     * Create Unique Arrays using an md5 hash
     *
     * @param array $array
     * @param boolean $preserveKeys
     * @return array
     */
    public function arrayUnique($array, $preserveKeys = false)
    {
        // Unique Array for return
        $arrayRewrite = array();
        // Array with the md5 hashes
        $arrayHashes = array();

        foreach ($array as $key => $item) {
            // Serialize the current element and create a md5 hash
            $hash = md5(serialize($item));

            // If the md5 didn't come up yet, add the element to
            // to arrayRewrite, otherwise drop it
            if (!isset($arrayHashes[$hash])) {
                // Save the current element hash
                $arrayHashes[$hash] = $hash;

                // Add element to the unique Array
                if ($preserveKeys) {
                    $arrayRewrite[$key] = $item;
                } else {
                    $arrayRewrite[] = $item;
                }
            }
        }

        return $arrayRewrite;
    }

    public function execute()
    {
        $result = array();
        $year = array();
        $params = $this->request->getPost();
        $productCollection = $this->productFactory->create()->getCollection();
        $productCollection->addAttributeToSelect('tradein_year');

        if (isset($params['car_make_id']) && isset($params['car_model'])) {
            $productCollection->addCategoriesFilter(array('in' => array($params['car_make_id'])));
            $productCollection->addAttributeToFilter('tradein_model', $params['car_model']);
            foreach ($productCollection->getData() as $product) {
                $productModel = $this->productFactory->create()->load($product['entity_id']);
                if ($productModel->getTradeinYear()) $result[] = array('year' => $productModel->getTradeinYear());
            }

            foreach ($result as $key => $row) {
                $year[$key]  = $row['year'];
            }

            array_multisort($year, SORT_DESC, $result);
            $result = $this->arrayUnique($result);
            echo json_encode($result);
        }
    }
}