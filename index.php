<?php
	//動画のディレクトリ
	$movie = "Videos/実験/AV1";
	//サイトタイトル
	$SiteTitle = "ワイの動画";
	//httpを使用するかhttpsを使用するか指定
	#$protocol = "http";
	//テンプレートのディレクトリ
	$template = "template";
	//デフォルトサムネイル
	//$DefaultThumb = "./thumb.jpg";
	//サムネイル用フォント
	$font = "./GenShinGothic-Medium.ttf";
	//テンプレートファイル
	$TemplateFile['index'] = "index_template.html";
	$TemplateFile['menu'] = "menu_template.html";
	$TemplateFile['breadcrumbs'] = "breadcrumbs_template.html";
	$TemplateFile['subnav'] = "subnav_template.html";
	$TemplateFile['contents'] = "contents_template.html";
	$TemplateFile['episode'] = "episode_template.html";

  //パラメータをセット
	$param['Category'] = $_GET['category'];
	$param['Series'] = $_GET['series'];
	$param['Episode'] = $_GET['episode'];
	#$param['ServerURL'] = $protocol."://".$_SERVER["HTTP_HOST"]."/";
	$param['ServerURL'] = "./";

	//トップページ
	if( is_null($param['Category']) && is_null($param['Series']) && is_null($param['Episode']) ){
        $CategoryList = category_pickup( "./".$movie )[0];
				$ThumbnailURL = category_pickup( "./".$movie)[1];
        $param['menu'] = menu_output($param, $CategoryList, $template, $TemplateFile['menu']);

        $param['Breadcrumbs'] = breadcrumbs_output( $param, $param['Category'], $param['Series'], $param['Episode'], $template, $TemplateFile['breadcrumbs'] );

        $ContentsList = category_pickup( "./".$movie )[0];
        $param['ContentsList'] = contents_output($param, $ContentsList, $ThumbnailURL, $template, $TemplateFile['contents']);

        $param['PageTitle'] = $SiteTitle;
        $TemplateFileLocation = $template."/".$TemplateFile['index'];
        echo template_engin($TemplateFileLocation, $param);
	}

	//シリーズ一覧ページ
	else if ( $param['Category'] != NULL && is_null($param['Series']) && is_null($param['Episode']) ){
        $CategoryList = category_pickup( "./".$movie )[0];
        $param['menu'] = menu_output($param, $CategoryList, $template, $TemplateFile['menu']);

        $param['Breadcrumbs'] = breadcrumbs_output( $param, $param['Category'], $param['Series'], $param['Episode'], $template, $TemplateFile['breadcrumbs'] );

        $SeriesList = series_pickup( "./".$movie, $param['Category'] )[0];
				$ThumbnailURL = series_pickup( "./".$movie, $param['Category'] )[1];

        $param['ContentsList'] = contents_output($param, $SeriesList, $ThumbnailURL, $template, $TemplateFile['contents']);

        $param['PageTitle'] = htmlspecialchars ( $param['Category']." | ".$SiteTitle );
        $TemplateFileLocation = $template."/".$TemplateFile['index'];
        echo template_engin($TemplateFileLocation, $param);
	}

	//エピソード一覧ページ
	else if ( $param['Category'] != NULL && $param['Series'] != NULL && is_null($param['Episode']) ){
        $CategoryList = category_pickup( "./".$movie )[0];
        $param['menu'] = menu_output($param, $CategoryList, $template, $TemplateFile['menu']);

        $param['Breadcrumbs'] = breadcrumbs_output( $param, $param['Category'], $param['Series'], $param['Episode'], $template, $TemplateFile['breadcrumbs'] );

        $EpisodeList = episode_pickup( "./".$movie, $param['Category'], $param['Series'] )[0];
				$EpisodeList = preg_replace( '/\.[^.]*$/', '', $EpisodeList );

				$ThumbnailURL = episode_pickup( "./".$movie, $param['Category'], $param['Series'] )[2];

        $param['ContentsList'] = contents_output($param, $EpisodeList, $ThumbnailURL, $template, $TemplateFile['contents']);

        $param['PageTitle'] = htmlspecialchars ( $param['Category']." | ".$param['Series']." | ".$SiteTitle );
        $TemplateFileLocation = $template."/".$TemplateFile['index'];
        echo template_engin($TemplateFileLocation, $param);
	}

	//動画視聴ページ
	else if ( $param['Category'] != NULL && $param['Series'] != NULL && $param['Episode'] != NULL ){
        $param['Category'] = htmlspecialchars_decode( $param['Category'] );
        $param['Series'] = htmlspecialchars_decode( $param['Series'] );
        $param['Episode'] = htmlspecialchars_decode( $param['Episode'] );

				$EpisodeList = episode_pickup( "./".$movie, $param['Category'], $param['Series'] );
				$EpisodeList[0] = preg_replace( '/\.[^.]*$/', '', $EpisodeList[0] );
				$i = 0;
				foreach ( $EpisodeList[0] as $value ){
					if ( $value == $param['Episode'] ){
						$param['Extension'] = $EpisodeList[1][$i];
						$param['ThumbnailURL'] = $EpisodeList[2][$i];
						break;
					}
					$i++;
				}


        $MovieURL = $movie."/".$param['Category']."/".$param['Series']."/".$param['Episode'].".".$param['Extension'];
        $MovieURL = rawurlencode( $MovieURL );

        $param['MovieURL'] = $param['ServerURL'].$MovieURL;
        $CategoryList = category_pickup( "./".$movie )[0];

        $param['menu'] = menu_output($param, $CategoryList, $template, $TemplateFile['menu']);

        $param['Breadcrumbs'] = breadcrumbs_output( $param, $param['Category'], $param['Series'], $param['Episode'], $template, $TemplateFile['breadcrumbs'] );

        $param['SubNav'] = subnav_output( $param, $EpisodeList[0], $param['Category'], $param['Series'], $param['Episode'],$template, $TemplateFile['subnav'] );

        $param['PageTitle'] = htmlspecialchars ( $param['Series']." | ".$param['Episode']." | ".$SiteTitle );
        $TemplateFileLocation = $template."/".$TemplateFile['episode'];
        echo template_engin($TemplateFileLocation, $param);
}

//カテゴリー直下動画視聴ページ
else if ( $param['Category'] != NULL && $param['Series'] == NULL && $param['Episode'] != NULL ){
				$param['Category'] = htmlspecialchars_decode( $param['Category'] );
				$param['Episode'] = htmlspecialchars_decode( $param['Episode'] );

				$EpisodeListTemp = series_pickup( "./".$movie, $param['Category'] );

				$j = 0;
				for( $i = 0; $i < count( $EpisodeListTemp[0] );$i++ ){
					$dir = "./".$movie."/".$param['Category']."/".$EpisodeListTemp[0][$i];
					if( is_file( $dir ) ){
						$EpisodeList[0][$j] = $EpisodeListTemp[0][$i];
						$EpisodeList[1][$j] = $EpisodeListTemp[1][$i];
						$j++;
					}
				}

				$i = 0;
				foreach ( $EpisodeList[0] as $value ){
					$value = preg_replace( '/\.[^.]*$/', '', $value );
					if ( $value == $param['Episode'] ){
						$param['Extension'] = pathinfo( $EpisodeList[0][$i] )['extension'];
						$param['ThumbnailURL'] = $EpisodeList[1][$i];
						break;
					}
					$i++;
				}

				$MovieURL = "./".$movie."/".$param['Category']."/".$param['Episode'].".".$param['Extension'];
				$MovieURL = rawurlencode( $MovieURL );
				$param['MovieURL'] = $param['ServerURL'].$MovieURL;

				$CategoryList = category_pickup( "./".$movie )[0];
				$param['menu'] = menu_output($param, $CategoryList, $template, $TemplateFile['menu']);

				$param['Breadcrumbs'] = breadcrumbs_output( $param, $param['Category'], $param['Series'], $param['Episode'], $template, $TemplateFile['breadcrumbs'] );

				$param['Series'] = $param['Category'];
				$EpisodeList[0] = preg_replace( '/\.[^.]*$/', '', $EpisodeList[0] );
				$param['SubNav'] = subnav_output( $param, $EpisodeList[0], $param['Category'], $param['Series'], $param['Episode'],$template, $TemplateFile['subnav'] );
				$param['SubNav'] = mb_str_replace( "&amp;series=".rawurlencode( $param['Category'] ), "", $param['SubNav'] );

				$param['PageTitle'] = htmlspecialchars ( $param['Category']." | ".$param['Episode']." | ".$SiteTitle );
        $TemplateFileLocation = $template."/".$TemplateFile['episode'];
        echo template_engin($TemplateFileLocation, $param);
}

//リダイレクト
else {
				header("Location: ./");
				exit;
}


    //テンプレートエンジン
    function template_engin($URL,$dataArray){
        $html = file_get_contents($URL);
        foreach ($dataArray as $key => $value){
            $value = str_replace( '{', '&#123;', $value);
            $value = str_replace( '}', '&#125;', $value);
            $html = str_replace( '{{'.$key.'}}', $value, $html);
        }
        $html=preg_replace('/{{.*}}/','',$html);
        return $html;
    }

    //カテゴリ一覧を取得
    function category_pickup($dir){
				global $DefaultThumb;
        $ls = "./".$dir;
        exec ( "ls \"".$ls."\"", $CategoryListTemp );
        sort ( $CategoryListTemp, SORT_NATURAL );

				foreach( $CategoryListTemp as $value ){
					if( strpos( $value, '.jpg' ) === false ){
						$CategoryList[] = $value;
						$ThumbnailURL = $dir."/".$value."/".$value.".jpg";
						if( file_exists( $ThumbnailURL ) ) {
							$ThumbnailList[] = rawurlencode( $ThumbnailURL );
						} else if( empty( $DefaultThumb ) && is_file( $ls."/".$value ) ) {
							$ThumbnailList[] = thumbnail_generate( pathinfo( $value )['filename'] );
							$CategoryList[$i] = pathinfo( $value )['filename'];
						} else if ( empty( $DefaultThumb ) && is_dir( $ls."/".$value ) ){
							$ThumbnailList[] = thumbnail_generate( $value );
						} else {
							$ThumbnailList[] = $DefaultThumb;
						}
					}
				}

        return [$CategoryList, $ThumbnailList];
    }

    //シリーズ一覧を取得
    function series_pickup($dir,$Category){
				global $DefaultThumb;
        $Category = htmlspecialchars_decode( $Category );

        $ls = "./".$dir."/".$Category;
        exec ( "ls \"".$ls."\"", $SeriesListTemp );
        sort ( $SeriesListTemp, SORT_NATURAL );

				foreach( $SeriesListTemp as $value ){
					if( strpos( $value, '.jpg' ) === false ){
						$SeriesList[] = $value;
						if( is_dir( $ls."/".$value ) ){
							$ThumbnailURL = $dir."/".$Category."/".$value."/".$value.".jpg";
						}
						else if( is_file( $ls."/".$value ) ){
							$ThumbnailURL = $dir."/".$Category."/".pathinfo( $value )['filename'].".jpg";
						}

						if( file_exists( $ThumbnailURL ) ) {
							$ThumbnailList[] = rawurlencode( $ThumbnailURL );
						} else if( empty( $DefaultThumb ) && is_file( $ls."/".$value ) ) {
							$ThumbnailList[] = thumbnail_generate( pathinfo( $value )['filename'] );
						} else if ( empty( $DefaultThumb ) && is_dir( $ls."/".$value ) ){
							$ThumbnailList[] = thumbnail_generate( $value );
						} else {
							$ThumbnailList[] = $DefaultThumb;
						}
					}
				}
        return [$SeriesList, $ThumbnailList];
    }

    //エピソード一覧を取得
    function episode_pickup($dir,$Category,$Series){
				global $DefaultThumb;
        $Category = htmlspecialchars_decode( $Category );
        $Series = htmlspecialchars_decode( $Series );

        $ls = "./".$dir."/".$Category."/".$Series;
        exec ( "ls \"".$ls."\"", $EpisodeListTemp );
        sort ( $EpisodeListTemp, SORT_NATURAL );

				foreach( $EpisodeListTemp as $value ){
					if( strpos( $value, '.jpg' ) === false ){
						$EpisodeList[] = $value;
						$ThumbnailURL = $dir."/".$Category."/".$Series."/".pathinfo( $value )['filename'].".jpg";
						if( file_exists( $ThumbnailURL ) ) {
							$ThumbnailList[] = rawurlencode( $ThumbnailURL );
						} else if( empty( $DefaultThumb ) ) {
							$ThumbnailList[] = thumbnail_generate( pathinfo( $value )['filename'] );
						} else {
							$ThumbnailList[] = $DefaultThumb;
						}
					}
				}

				foreach( $EpisodeList as $value ){
					$Extension[] = pathinfo( $value )['extension'];
				}

        $EpisodeList = str_replace( $Extension, "", $EpisodeList );
        return [$EpisodeList, $Extension, $ThumbnailList];
    }

    //メニューを出力
    function menu_output($param, $CategoryList, $template, $TemplateFile){
        foreach ($CategoryList as $value){
            $value = htmlspecialchars( $value );
            $param['CategoryList'] = $value;

            $value = rawurlencode( $value );
            $param['CategoryURL'] = "?category=".$value;

            $TemplateFileLocation = $template."/".$TemplateFile;
            $menu .= template_engin($TemplateFileLocation, $param);
        }
        return $menu;
    }

    //パンくずリスト出力
    function breadcrumbs_output( $param, $Category, $Series, $Episode, $template, $TemplateFile ){
        $TemplateFileLocation = $template."/".$TemplateFile;

        if ( $Category != null ){
            $param['Breadcrumbs'] = htmlspecialchars( $Category );
            $param['BreadcrumbsURL'] = "?category=".rawurlencode( $param['Breadcrumbs'] );
            $breadcrumbs .= template_engin($TemplateFileLocation, $param);
        }
        if ( $Series != null ){
            $param['Breadcrumbs'] = htmlspecialchars( $Series );
            $param['BreadcrumbsURL'] .= "&amp;series=".rawurlencode( $param['Breadcrumbs'] );
            $breadcrumbs .= template_engin($TemplateFileLocation, $param);
        }
            if ( $Episode != null ){
            $param['Breadcrumbs'] = htmlspecialchars( $Episode );
            $param['BreadcrumbsURL'] .= "&amp;episode=".rawurlencode( $param['Breadcrumbs'] );
            $breadcrumbs .= template_engin($TemplateFileLocation, $param);
        }

        return $breadcrumbs;
    }

    //サブナビゲーション出力
    function subnav_output($param, $EpisodeList, $Category, $Series, $Episode, $template, $TemplateFile ){
        $TemplateFileLocation = $template."/".$TemplateFile;

        $i = 0;
        $count = count( $EpisodeList ) - 1;

        foreach( $EpisodeList as $value ){
            if ( $value == $Episode && $i == 0 && $count > 0 ){
                $Category = htmlspecialchars( $Category );
                $Series = htmlspecialchars( $Series );
                $Episode = htmlspecialchars( $Episode );

								$param['SubNav'] = $EpisodeList[$i];
								$param['SubNavURL'] = "#";
								$SubNavList .= template_engin($TemplateFileLocation, $param);

                $param['SubNav'] = $Series;
                $param['SubNavURL'] = "?category=".rawurlencode( $Category )."&amp;series=". rawurlencode( $Series );
                $SubNavList .= template_engin($TemplateFileLocation, $param);

                $param['SubNav'] = $EpisodeList[$i + 1];
                $param['SubNavURL'] = $param['SubNavURL']."&amp;episode=".rawurlencode( $EpisodeList[$i + 1] );
                $SubNavList .= template_engin($TemplateFileLocation, $param);

                break;
            } else if ( $value == $Episode && $i == $count && $count > 0 ) {
                $Category = htmlspecialchars( $Category );
                $Series = htmlspecialchars( $Series );
                $Episode = htmlspecialchars( $Episode );

                $param['SubNav'] = $EpisodeList[$i - 1];
                $param['SubNavURL'] = "?category=".rawurlencode( $Category )."&amp;series=".rawurlencode( $Series )."&amp;episode=".rawurlencode( $EpisodeList[$i - 1] );
                $SubNavList .= template_engin($TemplateFileLocation, $param);

                $param['SubNav'] = $Series;
                $param['SubNavURL'] = "?category=".rawurlencode( $Category )."&amp;series=".rawurlencode( $Series );
                $SubNavList .= template_engin($TemplateFileLocation, $param);

								$param['SubNav'] = $EpisodeList[$i];
								$param['SubNavURL'] = "#";
								$SubNavList .= template_engin($TemplateFileLocation, $param);

                break;
            } else if ( $value == $Episode && $i < $count && $count > 0 ) {
                $Category = htmlspecialchars( $Category );
                $Series = htmlspecialchars( $Series );
                $Episode = htmlspecialchars( $Episode );

                $param['SubNav'] = $EpisodeList[$i - 1];
                $param['SubNavURL'] = "?category=".rawurlencode( $Category )."&amp;series=".rawurlencode( $Series )."&amp;episode=".rawurlencode( $EpisodeList[$i - 1] );
                $SubNavList .= template_engin($TemplateFileLocation, $param);

                $param['SubNav'] = $Series;
                $param['SubNavURL'] = "?category=".rawurlencode( $Category )."&amp;series=".rawurlencode( $Series );
                $SubNavList .= template_engin($TemplateFileLocation, $param);

                $param['SubNav'] = $EpisodeList[$i + 1];
                $param['SubNavURL'] = $param['SubNavURL']."&amp;episode=".rawurlencode( $EpisodeList[$i + 1] );
                $SubNavList .= template_engin($TemplateFileLocation, $param);

                break;
            } else if ( $value == $Episode && $count == 0 ) {
                $Category = htmlspecialchars( $Category );
                $Series = htmlspecialchars( $Series );

								$param['SubNav'] = $EpisodeList[$i];
								$param['SubNavURL'] = "#";
								$SubNavList .= template_engin($TemplateFileLocation, $param);

                $param['SubNav'] = $Series;
                $param['SubNavURL'] = "?category=".rawurlencode( $Category )."&amp;series=".rawurlencode( $Series );
                $SubNavList .= template_engin($TemplateFileLocation, $param);

								$param['SubNav'] = $EpisodeList[$i];
								$param['SubNavURL'] = "#";
								$SubNavList .= template_engin($TemplateFileLocation, $param);
            }

            $i++;
        }

        return $SubNavList;
    }

    //コンテンツ一覧を出力
    function contents_output($param, $ContentsList, $ThumbnailURL, $template, $TemplateFile){
				global $movie;
				$dir = "./".$movie."/".$param['Category'];
				$i = 0;
        foreach ($ContentsList as $value){
            $param['ContentsList'] = htmlspecialchars_decode( $value ).$param['Extension'];
            $param['ContentsURL'] = "?category=".rawurlencode( $value );
						$param['ThumbnailURL'] = $ThumbnailURL[$i];

            if ( $param['Category'] != NULL && is_null($param['Series']) && is_null($param['Episode']) && is_dir( $dir."/".$value ) ) {
                $param['ContentsURL'] = "?category=".rawurlencode( $param['Category'] )."&series=".rawurlencode( $value );
            }
						else if ( $param['Category'] != NULL && is_null($param['Series']) && is_null($param['Episode']) && is_file( $dir."/".$value ) ) {
								$value = pathinfo( $value )['filename'];
								$param['ContentsList'] = $value;
                $param['ContentsURL'] = "?category=".rawurlencode( $param['Category'] )."&episode=".rawurlencode( $value );
            }
            else if ( $param['Series'] != NULL && $param['Series'] != NULL && is_null($param['Episode']) ) {
                $param['ContentsURL'] = "?category=".rawurlencode( $param['Category'] )."&series=".rawurlencode( $param['Series'] )."&episode=".rawurlencode( $value );
						}
						else if ( $param['Series'] != NULL && $param['Series'] != NULL && is_null($param['Episode']) ) {
		            $param['ContentsURL'] = "?category=".rawurlencode( $param['Category'] )."&episode=".rawurlencode( $value );
            }
            $param['ContentsURL'] = htmlspecialchars( $param['ContentsURL'] );

            $TemplateFileLocation = $template."/".$TemplateFile;
            $contents .= template_engin($TemplateFileLocation, $param);

						$i++;
        }
        return $contents;
    }

		//サムネイル生成
		function thumbnail_generate($str){
			global $font;

			$width = 640;
			$height = 360;
			$font_size = 50;

			$im = imagecreate ($width, $height);
			$bg = ImageColorAllocate ($im, 255, 255, 255);

			$tb = imagettfbbox($font_size, 0, $font, $str);
			  $target_width = $width - 20;
			  $text_width = $tb[2] - $tb[6];
			  if ( $target_width < $text_width ){
			    $scale = $target_width / $text_width;
			      $font_size = $font_size * $scale;
			        $tb = imagettfbbox($font_size, 0, $font, $str);
			      }

			$x = ceil(($width - $tb[2]) / 2);
			$y = ceil(($height - $tb[5]) / 2);
			$font_color = ImageColorAllocate ($im, 100, 100, 100);
			ImageTTFText ($im, $font_size, 0, $x, $y, $font_color, $font, $str);

			ob_start();
			ImagePng( $im );
			$content = "data:image/png;base64,".base64_encode( ob_get_contents());
			ob_end_clean();
			ImageDestroy ($im);

			return $content;
		}

//マルチバイト対応str_replace
		function mb_str_replace($search, $replace, $haystack, $encoding="UTF-8"){
	    // 検索先は配列か？
	    $notArray = !is_array($haystack) ? TRUE : FALSE;
	    // コンバート
	    $haystack = $notArray ? array($haystack) : $haystack;
	    // 検索文字列の文字数取得
	    $search_len = mb_strlen($search, $encoding);
	    // 置換文字列の文字数取得
	    $replace_len = mb_strlen($replace, $encoding);

	    foreach ($haystack as $i => $hay){
	        // マッチング
	        $offset = mb_strpos($hay, $search);
	        // 一致した場合
	        while ($offset !== FALSE){
	            // 差替え処理
	            $hay = mb_substr($hay, 0, $offset).$replace.mb_substr($hay, $offset + $search_len);
	            $offset = mb_strpos($hay, $search, $offset + $replace_len);
	        }
	        $haystack[$i] = $hay;
	    }
	    return $notArray ? $haystack[0] : $haystack;
	}
?>
