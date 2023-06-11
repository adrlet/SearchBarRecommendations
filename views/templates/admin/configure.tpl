{*
* 2007-2022 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="panel">
	<h3><i class="icon icon-credit-card"></i> {l s='SearchbarRecommendations' mod='SearchbarRecommendations'}</h3>
	<p>
		<strong>{l s='Moduł dodający rekomendacje do wyszukiwarki.' mod='SearchbarRecommendations'}</strong><br />
		<div class="moduleInstruction">
			Priorytet rekomendacji:<br>
			<ol>
				<li>Produkty znajdujące się w kategorii, na liście produktów w module i jednocześnie posiadające wyszukiwaną frazę w nazwie
				<li>Produkty w kategorii posiadające wyszukiwaną frazę w nazwie
				<li>Produkty z listy produktów w module posiadające wyszukiwaną frazę w nazwie
				<li>Reszta produktów z listy produktów w module
			</ol>
			Gdy produkty mają ten sam priorytet zgodnie z powyższymi kryteriami ich pozycja jest uwarunkowana wybranym sortowaniem.<br>
			Kategorie można wpisywać w postaci listy oddzielonej przecinkami np. <i>13,94</i>.<br>
			Lista produktów w module zawiera unikatowe id produktów, nie można wpisać na listę produktu dwa razy.
		</div>
	</p>
</div>

<div class="panel recommendations_configuration">
	<h3><i class="icon icon-cogs"></i> {l s='Konfiguracja' mod='SearchbarRecommendations'}</h3>
	<div class="configuration_body">
		<div class="products_configuration">
			<div class="config_header">
				<div class="recommendedCategoryConfig">
					<label for="categoryId">Id rekomendowanych kategorii</label>
					<input name="categoryId" id="categoryId">
					<div class="changeCategory">Zmień</div>
				</div>
				<div class="categorySortOrder">
					<label for="sortingOptions">Sortowanie</label>
					<select name="sortingOptions" id="sortingOptions">
						<option disabled selected hidden></option>
						<option value="newest">Najnowsze</option>
						<option value="oldest">Najstarsze</option>
						<option value="categoryPos">Wg. pozycji w kategorii</option>
					</select>
				</div>
				<div class="newProduct">
					<label for="productId">Id produktu do dodania</label>
					<input type="number" name="productId" id="productId">
					<div class="addNewProduct">Dodaj</div>
				</div>
				<div class="reload">Odśwież</div>
			</div>
			<div class="currentProducts">
			</div>
		</div>
		<div class="category_configuration">
		
		</div>
	</div>
</div>