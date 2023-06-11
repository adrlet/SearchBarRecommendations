<?php

require_once __DIR__ . '/../AbstractEndpointController.php';

class SearchbarRecommendationsConfigurationModuleFrontController extends AbstractEndpointController
{
    protected function processGetRequest()
    {
        $request = $_GET['req'];
        $response = array(
            'isCorrect' => false,
            'message' => 'Niepoprawne żądanie',
            'data' => null
        );
        switch ($request) {
            case 'getProductIds':
                $response = array(
                    'isCorrect' => true,
                    'message' => '',
                    'data' => $this->getProductIds()
                );
                break;
            case 'getCurrentCategory':
                $response = array(
                    'isCorrect' => true,
                    'message' => '',
                    'data' => $this->getCurrentCategory()
                );
                break;
            case 'getSortOrder':
                $response = array(
                    'isCorrect' => true,
                    'message' => '',
                    'data' => $this->getSortOrder()
                );
                break;
            default:
                # code...
                break;
        }
        exit(json_encode($response));
    }

    protected function processPostRequest()
    {
        $request = $_POST['req'];
        $response = array(
            'isCorrect' => false,
            'message' => 'Niepoprawne żądanie'
        );
        switch ($request) {
            case 'addProductId':
                $response = $this->addProductId();
                break;
            case 'removeProductId':
                $response = $this->removeProductId();
                break;
            case 'changeCategory':
                $response = $this->changeCategory();
                break;
            case 'changeSortOrder':
                $response = $this->changeSortOrder();
            default:
                # code...
                break;
        }

        exit(json_encode($response));
    }

    protected function getProductIds()
    {
        $sql = '
                SELECT sr.`id_product` 
                FROM `' . _DB_PREFIX_ . 'searchbarRecommendations` sr';
        $productIds = Db::getInstance()->executeS($sql);
        return $productIds;
    }

    protected function getCurrentCategory()
    {
        $categoryId = Configuration::get('SEARCHBAR_RECOMMENDATIONS_CATEGORY');
        return $categoryId;
    }

    protected function getSortOrder(){
        $sortOrder = Configuration::get('SEARCHBAR_RECOMMENDATIONS_SORT');
        return $sortOrder;
    }

    protected function addProductId()
    {
        $productId = $_POST['productId'];
        if (!preg_match('/^[1-9][0-9]*$/', $productId)) {
            return array(
                'isCorrect' => false,
                'message' => 'Niepoprawny numer'
            );
        }
        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'searchbarRecommendations` VALUES (' . $productId . ')';
        $result = Db::getInstance()->execute($sql);
        return array(
            'isCorrect' => $result ? true : false,
            'message' => $result ? 'Dodano ' . $productId : 'Nieudane dodanie do bazy. Sprawdź czy produkt nie został już dodany.'
        );
    }
    protected function removeProductId()
    {
        $productId = $_POST['productId'];
        if (!preg_match('/^[1-9][0-9]*$/', $productId)) {
            return array(
                'isCorrect' => false,
                'message' => 'Niepoprawny numer'
            );
        }
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'searchbarRecommendations` sr WHERE sr.`id_product` = ' . $productId;
        $result = Db::getInstance()->execute($sql);
        return array(
            'isCorrect' => $result ? true : false,
            'message' => $result ? 'Usunięto ' . $productId : 'Nieudane usunięcie z bazy'
        );
    }
    protected function changeCategory()
    {
        $categoryId = $_POST['categoryId'];
        $oldValue = Configuration::get('SEARCHBAR_RECOMMENDATIONS_CATEGORY');
        if (!preg_match('/^[\d,]*$/', $categoryId)) {
            return array(
                'isCorrect' => false,
                'message' => 'Niepoprawny numer',
                'categoryValue' => $oldValue
            );
        }
        $categories = explode(',', $categoryId);
        foreach($categories as $key => $category){
            if(!preg_match('/^\d{1,}$/', $category)){
                unset($categories[$key]);
            }
        }
        unset($key, $value);
        $categoriesString = implode(',', $categories);
        $result = Configuration::updateValue('SEARCHBAR_RECOMMENDATIONS_CATEGORY', $categoriesString);
        return array(
            'isCorrect' => $result ? true : false,
            'message' => $result ? 'Ustawiono nowe kategorie' : 'Nieudana zmiana kategorii',
            'categoryValue' => $result ? $categoriesString : $oldValue
        );
    }
    protected function changeSortOrder(){
        $sortOrder = $_POST['sortOrder'];
        $oldValue = Configuration::get('SEARCHBAR_RECOMMENDATIONS_SORT');
        $allowedValues = ['newest', 'oldest', 'categoryPos'];
        $isCorrect = false;
        foreach($allowedValues as $value){
            if($sortOrder === $value){
                $isCorrect = true;
                break;
            }
        }
        unset($value);
        if($isCorrect){
            $result = Configuration::updateValue('SEARCHBAR_RECOMMENDATIONS_SORT', $sortOrder);
            return array(
                'isCorrect' => $result ? true : false,
                'message' => $result ? 'Ustawiono nowe sortowanie' : 'Nieudana zmiana sortowania',
                'sortValue' => $result ? $sortOrder : $oldValue
            );
        }
        return array(
            'isCorrect' => false,
            'message' => 'Niepoprawna wartość sortowania',
            'sortValue' => $oldValue
        );
    }
}
