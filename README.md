&lt;NOTE&gt;  
Sorry, this README is written in Japanese.  
Thank you.

参照： <http://www.smallmake.com/wp/?p=691>

PHP5.2上で動作するXMLを使った電子掲示板ソフトを公開します。SimpleXMLを使っています。あまり大きなファイルは扱えないですが、この記事の中で述べるような方法で「拡張クラス」を作ることで柔軟にいろいろなXMLフォーマットに対応させることができます。各HTMLフォーム要素が使え、入力バリデーションも提供しています。また、Cookeiやセッション、JavaScriptを使用しないので携帯電話などでも利用可能だと思います。バグ報告、ご要望などはコメント欄やメールでお寄せください。

##プログラム構成

以下の3つのクラスファイルとユーティリティで構成されています。  
- xmlbulletin.class.php : いわゆるコントローラにあたります  
- xmlbulletin.model.php : XMLへの入出力を担います  
- xmlbulletin.view.php : 一覧や入力フォームの表示を担います  
- utility.php : 翻訳機能やバリデーションのヘルパー機能を提供します

以下は、同梱されているサンプルです。  
- xmlbulletin.php : 利用ページのサンプルです  
- xmlbulletin.css : CSSサンプルです  
- local.ja : 日本語への翻訳テーブルのサンプルです  
- bbs.php : 電子掲示板を拡張する「拡張クラス」のサンプルです  
- atom.php : ATOMフォーマットを扱う「拡張クラス」のサンプルです  
- rss20.php : RSS2.0フォーマットを扱う「拡張クラス」のサンプルです  


##起動方法

上記サンプル xmlbulletin.php を使う場合、以下のように普通にブラウザでURL指定すると、デフォルトの掲示板になります。

>http://www.example.com/xmlbulletin.php

いろいろなパタンのXMLに対応させるために「拡張クラス」を使えますが、「拡張クラス」をソースの中でインクルードする必要はありません。下記のように / (スラッシュ）で区切って呼び出せば、インクルードされるようになっています。「拡張クラス」の作り方は後半で詳しく説明します。

>bbs.phpを使う→ http://www.example.com/xmlbulletin.php/bbs/  
atom.phpを使う→ http://www.example.com/xmlbulletin.php/atom/  
rss20.phpを使う→ http://www.example.com/xmlbulletin.php/rss20/  



##翻訳機能の役割

サンプルには local.ja というファイルがあります。xmlbulletinは基本的に表示は英語で作成されています。クラスファイルと同一ディレクトリにlocal.jaという名前のファイルを置くと、この中に記述されている翻訳テーブルに基づいて自動的に日本語表示になります。試しに、ファイルlocal.jaを削除して起動すると以下のようにボタンなどが英語表示の画面になります。

例えば、locale.ja内には以下のような記述があります。

`    “OK” = “実行”  
     “Cancel” = “キャンセル”  
     “Submit” =	 “登録”  
     “INDEX” =	 “一覧へ”  
     “Are you sure?” = “続行しますか？”  
     “ERROR” = “エラー”  
     “EDIT” = “編集”  
     “VIEW” = “詳細”  
     “DELETE” = “削除”  
     “UP” = “△”  
     “DOWN” = “▽”  
     “ADD” = “新規”  
     “LIST MODE” =	 “リスト表示”  
     “COLUMN MODE” =	 “カラム一覧表示”  
     “|&lt;TOP” = “|≪トップ”  
     “&lt;PREV” = “≪前へ”  
     “NEXT&gt;” =	 “次へ≫”  
     “LAST&gt;|” =	 “最後≫|”  
     “[%d/%d]” =	 “%dページ/全%dページ”` 

逆に言えば、これらのボタンなどの表示名を変更したり、何かのスタイルを付加したい場合にはプログラムソースの変更は必要なく、locale.jaを変更していただければよいことになります。  
また、locale.ja の .ja はブラウザの言語環境に対応しています。 具体的には、$_SERVER['HTTP_ACCEPT_LANGUAGE']で取得される文字列の最初の2文字に対応させています。日本語環境ではsafariなどでは ‘ja’ そのものが、FireFoxなどでは ‘ja_JP’ が取得されますので、これらに関して ‘locale.ja’ が採用されます。英語環境なら ‘en’ あるいは ‘en-us’ などが取得されるので、 ‘locale.en’ を作れば、英語表示の変更にも使えます。locale.ja と locale.en の両方を設置しておけば、それぞれの環境で違った表示にすることができます。  
なお、locale.ja については現状試験的な機能です。将来的にファイルの記述フォーマットや設置位置の仕様変更の可能性があります（WordPressなどで使っているgettext.phpを検討中）。  

##自分のPHPへのxmlbulletinの組み込み方法

添付の xmlbulletine.php は、組み込み方のもっともシンプルな一例です。現実問題としては、サイドバーのあるページや、その他いろいろな要素を持つページの中に掲示板を組み込みたいところだと思います。以下のようにPHPコードを挿入すれば、基本的にどんなPHPにもこのシステムを組み込むことができます。  
１）ファイルの冒頭に記述  
以下のコードを必ず冒頭で行ってください。  

`    <?php  
    include_once(“utility.php”);
    include_once(“xmlbulletin.class.php”);  
    $xb = new Xmlbulletin;  
    ?>`

２）タイトルの記述  
必須というわけではないですが&lt;title&gt;タグに以下のように記述すれば、「拡張クラス」毎に設定したタイトルになります。

`    <title><?php echo $xb->error ? __(‘ERROR’) : __($xb->model->title); ?></title>`

３）<head>内に記述
以下を<head>内に記述することで、デフォルトまたはカスタマイズしたCSSファイルへのlinkを挿入することができます。

`<?php echo $xb->html_head; ?>`

４）掲示板の表示
掲示板を表示する場所に以下のコードを挿入してください。

`<?php  
    if($xb->error) {  
     echo __($xb->error);  
    } else {  
    $xb->display();  
   }  
?>`

以上のコード挿入を行ってください。
xmlbulletineのセットのファイル群をそのPHPファイルと同じディレクトリにおいて、そのPHPファイルをブラウザで呼び出します。「拡張クラス」を作った場合は / (スラッシュ)で区切って指定します（※「起動方法」参照）

##「拡張クラス」の作り方

さて、いよいよいろいろなXMLフォーマットに対応させるための「拡張クラス」の作り方です。「拡張クラス」を適切に記述することで、XMLの構造の指定やアイテムへの属性の付加、入力バリデーション設定、および表示フォームのタイプやオプション項目の指定などを行うことができます。
「拡張クラス」ファイルには2つclassのextendsを記述します。XMLファイルへの入出力を行うclass XmlbulletinModel のextendsクラスの部分と、一覧や入力フォームの表示を行うclass XmlbulletinView の extendsクラス部分です。


##拡張クラス model の書き方

class XmlbulletinModel の拡張クラスとしてclass modelを作ります。class名は modelでなければなりません。  
以下の既定の変数を記述します。必須でないものは省略しても結構です。各変数について、後に詳述します。

変数名	必須	概要
$title	 	タイトルとなる文字列を指定  
$xml_file_name	 	XMLファイル名(省略:拡張クラスファイル名+.xml)  
$xml_file_path	 	XMLファイルのディレクトリ(省略:クラスと同じディレクトリ)  
$fore	○	XMLのヘッダ部分となるノード構造を配列で記述  
$fore_attributes	 	上記の構造の中でノード属性を付けるものの設定  
$item_parent	○	アイテムの親となるノードのタグ名を指定  
$item	○	XMLのアイテムとなるノード構造を配列で記述  
$item_attributes	 	アイテムの構造の中でノード属性を付けるものの設定  
$item_validations	 	アイテムの各ノードのバリデーションを設定  
　

####$title

ページのタイトルとなる文字列を記述します。翻訳機能の対象になっています。もちろん翻訳機能を使わない場合は日本語でそのまま記述しても結構です。

`public $title = "Bulletine Board System";`
　

####$fore

XMLにはたいていの場合、データ本体となる繰り返しのアイテムの前に親または同レベルのノードとしてタイトルなどを記述します。その部分の構造を配列で記述します。配列は階層構造になっても大丈夫です。その構造がそのままXMLの構造として反映されます。これは必須です。  
配列のkey名がタグに、valueがそのタグの内容になります。  
value側にはXML生成時に設定されるデフォルトの文字列を記述できます。  
この文字列には、`｛｝`（中カッコ）でくくってPHPの何か値を返すファンクションを含めることができます。例えば`{date(“Y-m-d”)}`とすれば、このXMLの生成時の日付が入ります。  

`public $fore = array(  
	'bbs' => array(  
		'title' => 'Bulletine Board System',  
		'subtitle' => '',  
		'updated' => '{date("Y-m-d H:i:s")}',  
		'id' => 'tag:smallmake.com,{date("Y-m-d")},{uniqid()}',  
		'generator' => 'xmlbulletine'  
	)  
);`
　

####$fore_attributes

`$fore`で記述したノードにXML生成時にXML属性を付加することができます。ここでは構造を表すのには配列を使わず、パス記述でノードを指定します。例えば、ルートであるbbsの下のsubtitleのノードのパスは ‘/bbs/subtitle’ となります。このパスをキーとして、その下に配列をつくり、その配列のkeyで属性名、valueで属性値を指定します。

`public $fore_attributes = array (  
	'/bbs/subtitle' => array('type' => 'html')  
);  `
　

####$item_parent

この後のアイテムノードの親ノードの名前を指定します。親ノードは$foreに記述されていて、それを指していなければなりません。これは必須です。

`public $item_parent = "bbs";`
　

####$item

アイテムのノード構造を配列を使って記述します。$foreと同様、配列のkey名がタグに、value側にはアイテムの生成時に設定されるデフォルトの文字列を記述できます。｛｝（中カッコ）での記述は$foreと同様です。

`public $item = array(  
	'entry' => array (  
			'title' => '',  
			'id'		=> 'tag:smallmake.com,{date("Y-m-d")},{uniqid()}',  
			'published' => '{date("Y-m-d H:i:s")}',  
			'author' => array(  
				'name' => '',  
				'uri'  => '',  
				'email' => '',  
				'gender' => '',  
				'age' => '',  
				'pc' => ''  
			),  
			'content' => ''  
		)  
);`
　

####$item_attributes

$itemで記述したノードにアイテムの生成時にXML属性を付加することができます。ここでは構造を表すのには$fore_attributesと同様に配列を使わず、パス記述でノードを指定します。  
“type”=”html”属性はこのシステムでは重要な役割をします。”type”=”html”属性のノードは、カラム一覧やリスト表示の時にHTML特殊文字をエスケープしないように出力されます。例えば、新規や編集フォームで  
「&lt;a href=”http://www.smallmake.com”&gt;このサイト&lt;/a&gt;」  
と入力して登録すると、カラム一覧やリストでの表示は”type”=”html”属性のある項目の場合  
「このサイト」  
と表示され、この属性のないものはそのまま   
「&lt;a href=”http://www.smallmake.com”&gt;このサイト&lt;/a&gt;」  
と表示されることになります。  

`public $item_attributes = array(  
    'entry/content' => array('type' => 'html')  
);`
　

####$item_validations

バリデートしたいノードをパスで指定します。バリデートしたいものだけ記述すればよいです。そのノードパスをkeyとする配列を作り、その下にさらに配列を作り、そのkeyとしてバリデートしたいルールを、そしてその　valueにバリデーションの結果エラーとなったな場合のメッセージを記述します。ただし、ルールに何らかの条件値を設定しなければならない場合（例えば最大値や最小値の比較バリデーション）は、valueをさらに配列にして’param’=>でその条件値、’message’=>でエラーメッセージを指定します。つまり以下の2パターンがあるということです。  
１） ’ノードパス’ => array(‘ルール’=>’エラーメッセージ’);  
２） ’ノードパス’ => array(‘ルール’=>array(‘param’=>’条件値’, message=>’エラーメッセージ’);  
バリデーションで指定できるルールと条件値などの一覧は下図を見てください。  

ルール	検証内容	条件値	備考  
require	必須入力にする	なし  	
email	メールアドレス  
の書式	“checkDNS”	”checkDNS”を指定した場合、ドメイン名をネットワークでチェック。  
zip	郵便番号の書式	“jp”, “us”	jp: 日本3桁-4桁、 us: 米国5桁-4桁  
phone	電話番号の書式	“jp”, “us”	jp:日本[2〜5桁]-[1〜5桁]-[3〜5桁]、 us:3桁-3桁-4桁  
date	日付書式	フォーマット	フォーマットにはPHPのdate関数のフォーマット文字列を使用。フォーマット文字列については http://jp2.php.net/manual/ja/function.date.phpを参照。指定がない場合、”Y-m-d” とする。”Y-m-d”は例えば “2010-03-14”のような形式を表す。  
max	最大数値	数値	最大値の数字を指定。それより大きい数字が入力されるとエラーとする。  
min	最小数値	数値	最小値の数字を指定。それより少ない数字が入力されるとエラーとする。  
maxLength	最大文字数	数値	最大文字数を指定。それより多い文字数が入力されるとエラーとする。  
minLength	最小文字数	数値	最小文字数を指定。それより少ない文字数が入力されるとエラーとする。  
alphaNumeric	半角英数字	なし	  
numeric	半角数字	なし	  
url	URL書式	なし	http: https: ftp: で始まるURL書式  
ip	IPアドレス書式	なし	  
creditCard	クレジットカード  
ナンバー書式	なし	  
ssn	米国社会保障番号	なし	  
custom	ユーザー指定の
文字列検証	正規表現	正規表現で検証文字列を指定する。  
`public $item_validations = array(  
  'entry/title' =>         array('require'=>'Title is required.'),  
  'entry/published' =>     array('date'=>array('param'=>'Y-m-d H:i:s',  
                                'message'=>'Invalid Date format(need Y-m-d H:i:s)')),  
  'entry/author/name' =>   array('require'=>'Your name is required.'),  
  'entry/author/uri' =>   array('url'=>'Invalid Site URL format.'),  
  'entry/author/email' => array('require'=>'Your E-Mail is required.',  
                                'email'=>'Invalid E-Mail format.'),  
  'entry/author/gender' =>   array('require'=>'Select your gender.'),  
  'entry/content' =>      array('require'=>'Comment is required.')  
);`  

##拡張クラス view の書き方

class XmlbulletinView の拡張クラスとしてclass viewを作ります。class名は viewでなければなりません。  
以下の既定の変数を記述します。全て省略可です。省略した場合、デフォルトもしくは無設定になります。各変数について、後に詳述します。

変数名	デフォルト	概要  
$edit_mode	TRUE	「編集」ボタン等の表示/非表示  
$sort_mode	descending	カラム一覧/リスト表示の表示順  
$line_per_page	10	リスト表示1ページの件数  
$column_per_page	5	カラム一覧1ページの件数  
$item_styles	無設定	フォームの要素のタイプ、ラベル、オプション値など  
　

####$edit_mode

これは編集用のボタンを表示するかどうかの指定です。閲覧だけする人たちのために用意したページでは「新規」「編集」「削除」といったボタンは不要です。そのような場合、FALSEにすると表示しません。  
TRUE : 編集用のボタン等を表示  
FALSE : 編集用のボタン等を隠す  

`public $edit_mode = true;`
　

####$sort_mode

カラム一覧やリスト表示の表示順の指定です。  
ascending : アイテムを入力した順に表示（古いものから順に表示）  
descending : アイテムを入力した順の逆順に表示（新しいものが上）  

`public $sort_mode = "descending";`
　

`$line_per_page` と `$column_per_page`

カラム一覧およびリスト表示のページングをします。1ページに表示する件数を指定できます。  
$line_per_page : リスト表示の場合の1ページの件数  
$column_per_page : カラム一覧表示の場合の1ページの件数  

`public $line_per_page = 10;  
public $column_per_page = 5;`
　

####$item_styles

フォームの要素のタイプ、ラベル、オプション値の一覧などの指定ができます。配列のkeyでノードパスで指定し、そのvalueを配列にして、keyで設定内容、valueで設定値を記述します。

設定内容	設定値	備考  
type	フォーム要素タイプ	HTMLタグの&lt;input type=で指定するタイプであるtext / radio / checkbox / hidden、あるいはselect, textareaを指定できます。hiddenにしたものはカラム一覧やリスト表示でも非表示になります。  
label	表示ラベル	HTMLタグの<label>で付ける表示ラベルを記述します。翻訳機能の対象ですが、翻訳の必要がなければ日本語をそのまま記述しても結構です  
options	選択項目（配列）	radio, checkbox, selectの場合の選択項目を配列で指定します。この配列のkeyがフォームのvalueとなり、配列のvalueは表示される文字列となります。表示文字列は翻訳機能の対象ですが、翻訳の必要がなければ日本語をそのまま記述しても結構です  
readonly	readonly属性	少し助長ですが’readonly’=&gt;’readonly’と記述します。設定する場合は必ずこの記述です。また、このフォーム要素にはstyle classとして class=”readonly”が付加されます。例えばCSSで .readonly = { background-color:#ccc;} などとすれば、readonlyのフォーム要素の背景をグレーにできます  
list	リスト表示指定	カラム一覧はhidden指定以外の項目を全て表示しますが、リスト表示に関してはその対象としたい項目について、この’list’=>trueを指定しなければなりません   
`public $item_styles = array(  
 'entry/title' =>     array('type'=>'text',   'label'=>'TITLE', 'list'=>true),  
 'entry/id' =>      array('type'=>'hidden'),
 'entry/published' =>   array('type'=>'text',   'label'=>'DATE', 'list'=>true,
                'readonly'=>'readonly'),  
 'entry/author/name' =>  array('type'=>'text',   'label'=>'NAME',   'list'=>true),
 'entry/author/uri' =>  array('type'=>'text',   'label'=>'SITE'),   
 'entry/author/email' => array('type'=>'text',   'label'=>'E-MAIL'),  
 'entry/author/gender'  => array('type'=>'radio',  'label'=>'GENDER', 'list'=>true,  
                'options'=> array('1'=>'Male', '2'=>'Female', '3'=>'Other')),  
 'entry/author/age'  => array('type'=>'select',  'label'=>'AGE',
                'options'=> array('0'=>'--', '10'=>'10´s',  
                '20'=>'20´s','30'=>'30´s','40'=>'40´s',  
                '50'=>'50´s','60'=>'60´s','70'=>'70´s',  
                '80'=>'80´s','90'=>'90´s')),  
 'entry/author/pc'  => array('type'=>'checkbox',  'label'=>'PC',   'list'=>true,  
                'options'=> array('Win'=>'Windows', 'Mac'=>'Machintosh',
                'Lnx'=>'Linux', 'Oth'=>'Others')),  
 'entry/content' =>   array('type'=>'textarea', 'label'=>'COMMENT')  
);`


##CSS作成情報

CSSに関しては、「拡張クラス」のファイル名と同じファイル名のCSSを設置すればそちらを優先します。例えば、「拡張クラス」bbs.phpを使う場合、bbs.cssというファイルを作って設置すれば、これを優先します。  
生成されるHTMLのスタイルclass名には一定の規則がありますので、それを元にCSSを作れば自由にレイアウトをカスタマイズできると思います。スタイルclass名の生成ルールを以下のようになります。 &gt; は 上下関係、| は並列設定です。  
カラム一覧： columns &gt; column  
リスト表示： columns &gt; table.list  
ボタン表示： actions &gt; ul &gt; li &gt; a  
フォーム表示： input | text,radio,checkbox,select,textarea | [各アイテムタグ名]  
フォーム表示の時、各フォーム要素を囲む divの class名に要素に応じて   text,radio,checkbox,select,textarea およびアイテムのタグ名そのものを付けています。CSSでタグ毎の設定することで細かな設定ができるのではないかと思います。  
