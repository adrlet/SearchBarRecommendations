<?php

require_once __DIR__ . '/../AbstractEndpointController.php';

class SearchbarRecommendationsSearchModuleFrontController extends AbstractEndpointController
{
    protected function processGetRequest()
    {
        $searchedPhrase = $_GET['q'];
        $isCorrectPhrase = preg_match('/^[^<>;=#{}]*$/ui', $searchedPhrase) && (strlen($searchedPhrase) > 2);
        $searchedCategoriesString = Configuration::get('SEARCHBAR_RECOMMENDATIONS_CATEGORY');
        $searchedCategories = explode(',', $searchedCategoriesString);
        $searchedCategoriesSql = '';
        foreach ($searchedCategories as $key => $category) {
            $searchedCategoriesSql = $searchedCategoriesSql ? $searchedCategoriesSql.' OR cp.`id_category` = '.(int)$category.' ' : 'cp.`id_category` = '.(int)$category.' ';
        }
        unset($key, $category);
        $sortOrder = Configuration::get('SEARCHBAR_RECOMMENDATIONS_SORT');
        $sortOrderSql = 'ORDER BY pl.`id_product` DESC ';
        $joinCategoryProduct = false;
        switch ($sortOrder) {
            case 'newest':
                $sortOrderSql = 'ORDER BY pl.`id_product` DESC ';
                break;
            case 'oldest':
                $sortOrderSql = 'ORDER BY pl.`id_product` ASC ';
                break;
            case 'categoryPos':
                $sortOrderSql = 'ORDER BY cp.`position` ASC, pl.`id_product` DESC ';
                $joinCategoryProduct = true;
                break;
            default:
                break;
        }
        $id_lang = $this->context->cookie->id_lang;
        $productsLimit = 3;
        $products = array();
        // search recommended category products
        if(count($searchedCategories) && $isCorrectPhrase){
            $sql = '
                SELECT DISTINCT pl.`id_product`, pl.`name` 
                FROM `' . _DB_PREFIX_ . 'product_lang` pl 
                JOIN `' . _DB_PREFIX_ . 'searchbarRecommendations` sr ON sr.`id_product` = pl.`id_product` 
                JOIN `' . _DB_PREFIX_ . 'product` p ON pl.`id_product` = p.`id_product` 
                JOIN `' . _DB_PREFIX_ . 'category_product` cp ON p.`id_product` = cp.`id_product` 
                WHERE pl.`id_lang` = '.(int)$id_lang.' 
                '.($searchedCategoriesSql ? 'AND ('.$searchedCategoriesSql.') ' : ' ').' 
                AND pl.`name` LIKE "%'.$searchedPhrase.'%" 
                AND p.`active` = 1 
                '.$sortOrderSql.'
                LIMIT '.$productsLimit;

            // test
            file_put_contents('sqltest.txt', $sql.PHP_EOL, FILE_APPEND);

            $products = Db::getInstance()->executeS($sql);
        }
        if(count($searchedCategories) && is_countable($products) && (count($products) < $productsLimit) && $isCorrectPhrase){
            $sql = '
                SELECT DISTINCT pl.`id_product`, pl.`name` 
                FROM `' . _DB_PREFIX_ . 'product_lang` pl 
                JOIN `' . _DB_PREFIX_ . 'product` p ON pl.`id_product` = p.`id_product` 
                JOIN `' . _DB_PREFIX_ . 'category_product` cp ON p.`id_product` = cp.`id_product` 
                WHERE pl.`id_lang` = '.(int)$id_lang.' 
                '.($searchedCategoriesSql ? 'AND ('.$searchedCategoriesSql.') ' : ' ').' 
                AND pl.`name` LIKE "%'.$searchedPhrase.'%" 
                AND p.`active` = 1 
                '.$sortOrderSql.'
                LIMIT '.$productsLimit;

            // test
            file_put_contents('sqltest.txt', $sql.PHP_EOL, FILE_APPEND);

            $newProducts = Db::getInstance()->executeS($sql);
            foreach ($newProducts as $newProduct){
                $isAlreadyInArray = false;
                foreach($products as $product){
                    if($product['id_product'] == $newProduct['id_product']){
                        $isAlreadyInArray = true;
                        break;
                    }
                }
                unset($product);
                if(!$isAlreadyInArray){
                    $products[] = $newProduct;
                }
                if(count($products) >= $productsLimit){
                    break;
                }
            }
            unset($newProduct);
        }
        // add matching predefined results
        if(is_countable($products) && (count($products) < $productsLimit) && $isCorrectPhrase){
            $sql = '
                SELECT DISTINCT pl.`id_product`, pl.`name` 
                FROM `' . _DB_PREFIX_ . 'product_lang` pl 
                JOIN `' . _DB_PREFIX_ . 'searchbarRecommendations` sr ON pl.`id_product` = sr.`id_product` 
                JOIN `' . _DB_PREFIX_ . 'product` p ON pl.`id_product` = p.`id_product` 
                '.($joinCategoryProduct ? 'JOIN `' . _DB_PREFIX_ . 'category_product` cp ON p.`id_product` = cp.`id_product` ' : '').'
                WHERE pl.`id_lang` = '.(int)$id_lang.' 
                AND p.`active` = 1 
                AND pl.`name` LIKE "%'.$searchedPhrase.'%" '
                .$sortOrderSql;

            // test
            file_put_contents('sqltest.txt', $sql.PHP_EOL, FILE_APPEND);

            $newProducts = Db::getInstance()->executeS($sql);
            foreach ($newProducts as $newProduct){
                $isAlreadyInArray = false;
                foreach($products as $product){
                    if($product['id_product'] == $newProduct['id_product']){
                        $isAlreadyInArray = true;
                        break;
                    }
                }
                unset($product);
                if(!$isAlreadyInArray){
                    $products[] = $newProduct;
                }
                if(count($products) >= $productsLimit){
                    break;
                }
            }
            unset($newProduct);
        }
        // add rest of results
        if(count($products) < $productsLimit && $isCorrectPhrase){
            $sql = '
                SELECT DISTINCT pl.`id_product`, pl.`name` 
                FROM `' . _DB_PREFIX_ . 'product_lang` pl 
                JOIN `' . _DB_PREFIX_ . 'searchbarRecommendations` sr ON pl.`id_product` = sr.`id_product` 
                JOIN `' . _DB_PREFIX_ . 'product` p ON pl.`id_product` = p.`id_product` 
                '.($joinCategoryProduct ? 'JOIN `' . _DB_PREFIX_ . 'category_product` cp ON p.`id_product` = cp.`id_product` ' : '').'
                WHERE pl.`id_lang` = '.(int)$id_lang.'
                AND p.`active` = 1 '
                .$sortOrderSql;

            // test
            file_put_contents('sqltest.txt', $sql.PHP_EOL, FILE_APPEND);

            $newProducts = Db::getInstance()->executeS($sql);
            foreach ($newProducts as $newProduct){
                $isAlreadyInArray = false;
                foreach($products as $product){
                    if($product['id_product'] == $newProduct['id_product']){
                        $isAlreadyInArray = true;
                        break;
                    }
                }
                if(!$isAlreadyInArray){
                    $products[] = $newProduct;
                }
                if(count($products) >= $productsLimit){
                    break;
                }
            }
        }
        // get additional product info
        $linkInstance = new Link();
        foreach($products as $key => $product){
            $instance = new Product($product['id_product'],false,$id_lang);
            $products[$key]['link'] = $instance->getLink();
            $products[$key]['price'] = Product::convertAndFormatPrice($instance->getPrice());
            $imageId = Product::getCover($product['id_product']);
            $products[$key]['image'] = $linkInstance->getImageLink(isset($instance->link_rewrite) ? $instance->link_rewrite : $instance->name, (int)$imageId['id_image']);
        }

        exit(json_encode($products));
    }

    protected function processPostRequest()
    {
        // do something then output the result
        exit(json_encode([
            'success' => true,
            'operation' => 'post'
        ]));
    }
}
