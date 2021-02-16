<?php


require __DIR__ . '../vendor/autoload.php';

use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSession;
use Google\AdsApi\AdWords\AdWordsSessionBuilder;
use Google\AdsApi\AdWords\v201809\cm\Language;
use Google\AdsApi\AdWords\v201809\cm\NetworkSetting;
use Google\AdsApi\AdWords\v201809\cm\Paging;
use Google\AdsApi\AdWords\v201809\o\AttributeType;
use Google\AdsApi\AdWords\v201809\o\IdeaType;
use Google\AdsApi\AdWords\v201809\o\LanguageSearchParameter;
use Google\AdsApi\AdWords\v201809\o\NetworkSearchParameter;
use Google\AdsApi\AdWords\v201809\o\RelatedToQuerySearchParameter;
use Google\AdsApi\AdWords\v201809\o\RequestType;
use Google\AdsApi\AdWords\v201809\o\SeedAdGroupIdSearchParameter;
use Google\AdsApi\AdWords\v201809\o\TargetingIdeaSelector;
use Google\AdsApi\AdWords\v201809\o\TargetingIdeaService;
use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\Common\Util\MapEntries;

class GetKeywordIdeas
{

    // If you do not want to use an existing ad group to seed your request, you
    // can set this to null.
    const AD_GROUP_ID = null;
    const PAGE_LIMIT = 500;

    public static function getkeywordIdeas(
        AdWordsServices $adWordsServices,
        AdWordsSession $session,
        $adGroupId,
        $keyword, 
        $results,
        $stat
    ) {
        $result = [];
        $targetingIdeaService = $adWordsServices->get($session, TargetingIdeaService::class);

        // Create selector.
        $selector = new TargetingIdeaSelector();
        if($stat==false){
            $selector->setRequestType(RequestType::IDEAS); // IDEAS
        }else{
            $selector->setRequestType(RequestType::STATS); // STATS
        }
        
        $selector->setIdeaType(IdeaType::KEYWORD);
        $selector->setRequestedAttributeTypes(
            [
                AttributeType::KEYWORD_TEXT,
                AttributeType::SEARCH_VOLUME,
                AttributeType::AVERAGE_CPC,
                AttributeType::COMPETITION,
                AttributeType::CATEGORY_PRODUCTS_AND_SERVICES
            ]
        );

        $paging = new Paging();
        $paging->setStartIndex(0);
        $paging->setNumberResults(10);
        $selector->setPaging($paging);

        $searchParameters = [];
        // Create related to query search parameter.
        $relatedToQuerySearchParameter = new RelatedToQuerySearchParameter();

           
        $relatedToQuerySearchParameter->setQueries(
            $keyword
        );
        $searchParameters[] = $relatedToQuerySearchParameter;

        // Create language search parameter (optional).
        // The ID can be found in the documentation:
        // https://developers.google.com/adwords/api/docs/appendix/languagecodes
        $languageParameter = new LanguageSearchParameter();
        $english = new Language();
        $english->setId(1000);
        $languageParameter->setLanguages([$english]);
        $searchParameters[] = $languageParameter;

        // Create network search parameter (optional).
        $networkSetting = new NetworkSetting();
        $networkSetting->setTargetGoogleSearch(true);
        $networkSetting->setTargetSearchNetwork(false);
        $networkSetting->setTargetContentNetwork(false);
        $networkSetting->setTargetPartnerSearchNetwork(false);

        $networkSearchParameter = new NetworkSearchParameter();
        $networkSearchParameter->setNetworkSetting($networkSetting);
        $searchParameters[] = $networkSearchParameter;

        // Optional: Use an existing ad group to generate ideas.
        if (!empty($adGroupId)) {
            $seedAdGroupIdSearchParameter = new SeedAdGroupIdSearchParameter();
            $seedAdGroupIdSearchParameter->setAdGroupId($adGroupId);
            $searchParameters[] = $seedAdGroupIdSearchParameter;
        }
        $selector->setSearchParameters($searchParameters);
        $selector->setPaging(new Paging(0, $results));

        // Get keyword ideas.
        $page = $targetingIdeaService->get($selector);

        // Print out some information for each targeting idea.
        $entries = $page->getEntries();
        if ($entries !== null) {
            foreach ($entries as $targetingIdea) {
                $data = MapEntries::toAssociativeArray($targetingIdea->getData());
                $keyword = $data[AttributeType::KEYWORD_TEXT]->getValue();
                $searchVolume = ($data[AttributeType::SEARCH_VOLUME]->getValue() !== null)
                    ? $data[AttributeType::SEARCH_VOLUME]->getValue() : 0;
                $averageCpc = $data[AttributeType::AVERAGE_CPC]->getValue();
                $competition = $data[AttributeType::COMPETITION]->getValue();
                $categoryIds = ($data[AttributeType::CATEGORY_PRODUCTS_AND_SERVICES]->getValue() === null)
                    ? $categoryIds = ''
                    : implode(
                        ', ',
                        $data[AttributeType::CATEGORY_PRODUCTS_AND_SERVICES]->getValue()
                    );

                    array_push($result, [$keyword, $searchVolume]);
                    
            }
        }
        return $result;
        
    }

    public function __construct()
    {
        
    }

    public function getMultipleKeywordIdeas($keywords, $results, $variants)
    {
        // Generate a refreshable OAuth2 credential for authentication.
        $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile()->build();

        // Construct an API session configured from a properties file and the
        // OAuth2 credentials above.
        $session = (new AdWordsSessionBuilder())->fromFile()->withOAuth2Credential($oAuth2Credential)->build();
        
        $keyArr = explode(',', $keywords);
        $r = [];

        foreach ($keyArr as $k) {
            $result = self::getkeywordIdeas(new AdWordsServices(), $session, self::AD_GROUP_ID, createVariants($k, $variants), $results, true);
            //$result = array_merge($result, self::getkeywordIdeas(new AdWordsServices(), $session, self::AD_GROUP_ID, $k, $results, false));
            array_push($r,$result);
        }
        
        return $r;
    }
}

function createVariants($keyword, $variants){
    $res = [$keyword];
    foreach($variants as $v){
        array_push($res, $keyword . " " . $v); 
    }
    return $res;
}
   
    

