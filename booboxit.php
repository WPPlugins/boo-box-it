<?php
/*
Plugin Name: Boo-Box It!
Version: 0.3
Plugin URI: http://www.richardbarros.com.br/blog/
Description: O <em>Boo-Box It!</em> insere links para o <a href="http://www.boo-box.com" target="_blank">Boo-Box</a> aumaticamente em todos os seus posts sem editá-los. Basta configurar seu código de afiliado, as palavras-chave e suas respectivas tags na opções do plug-in.

Author: Richard Barros
Author URI: http://www.richardbarros.com.br/blog/

*/

// Inclui a classe que cria funções de JSON em PHP 4+
$var = include ('JSON.php');

// CONFIG
$table_name = $wpdb->prefix . "bbit"; // Define o nome da tabela a ser criada.

function bbit_add_boo_head() {
	// Funcão adaptada do Plugin Boo-Box v0.4
	// Recupera opções de afiliação/ID definidas no painel de administração do WP
	echo "<script src=\"http://stable.boo-box.com/\"></script>";
}

/*
  Substitui palavras por links de acordo com as pre-definicoes das opcoes
*/
function bbit_replace($content) {
	global $wpdb;
    global $bbit_db_version;
    global $table_name;
	
	if (get_option('bbit_ligado') == 'on')
	{
		$mybbit_affiliated = get_option('bbit_affiliated');
		$mybbit_id = get_option('bbit_id');
		$mybbit_ligado = get_option('bbit_ligado');
		$once = get_option('bbit_once');
		$texto = $content;
		
		// Se o limite de apenas 1 resultado está ligado
		if ($once == 'on') { $limite = 0; } else { $limite = 10; }
		
		$obbits = json_decode( get_option("bbit_json"), true );
	

		//para cada resultado encontrado em $obbit
		if( count($obbits) ) foreach ($obbits as $word => $tags) {
			$m = 0;
			
			$texto = preg_replace("/(((?<=\.|,|\s|>)(?<!<(a|b))".$word."(?!.{0,30}<\/(a|b|strong)>)(?=\.|,|\s|<\/)))/i", "[bbit]".$word."[bbit]", $texto);

			$arraytexto = explode("[bbit]",$texto);

			foreach ($arraytexto as $key => $value)
			{
				
				if ($value == $word)
				{
					if ($m <= $limite)
					{
						$alteracao_pre="<a href=\"http://boo-box.com/link/aff:".$mybbit_affiliated."/uid:".$mybbit_id."/tags:".str_replace(" ", "+", $tags)."\" class=\"bbli\">";
						$alteracao_pos="<img src=\"http://boo-box.com/bbli\" alt=\"[bb]\" class=\"bbic\" /></a>";
						$arraytexto[$key] = $alteracao_pre.$word.$alteracao_pos;
					}
					$m++;
	
				}
			}
			$texto = implode ($arraytexto);
		}
		//return "<script src=\"http://stable.boo-box.com/\"></script>".$texto;
		return $texto;
	}
	else 
	{ 
		return $content;
	}

}

function bbit_add_options() {
	// Adiciona um novo menu nas Opções do WP
	add_options_page('Boo-Box it!', 'Boo-Box It!', 8, __FILE__, 'bbit_painel');
}


$bbit_db_version = "1.0";

function bbit_install () {
   global $wpdb;
   global $bbit_db_version;
   global $table_name;
}

function bbit_painel(){
	
	   global $wpdb;
	   global $bbit_db_version;
	   global $table_name;
    
	 if (isset($_POST['update_bbit'])) {
		
		$option_bbit_affiliated = $_POST['bbit_affiliated'];
		$option_bbit_id = $_POST['bbit_id'];
		$option_bbit_ligado = $_POST['bbit_ligado'];
		$option_bbit_once = $_POST['bbit_once'];

		update_option('bbit_affiliated', $option_bbit_affiliated);
		update_option('bbit_id', $option_bbit_id);
		update_option('bbit_ligado', $option_bbit_ligado);
		update_option('bbit_once', $option_bbit_once);
		?> <div class="updated"><p>Op&ccedil;&otilde;es atualizadas.</p></div> <?php
	 }

		
	 if (isset($_POST['excluir'])) {

        $json = json_decode( get_option("bbit_json"), true ); //true para retornar como array em vez de objeto
        unset( $json[ $_POST['bbit_word'] ] ); //remove o indice do array
        $json = json_encode( $json );
        update_option("bbit_json", $json); // salva o json no banco

		?> <div class="updated"><p>Excluido com sucesso.</p></div> <?php
     }

	 if (isset($_POST['addwords_bbit']) && $_POST['word'] && $_POST['tags'] ) {

        $json = json_decode( get_option("bbit_json"), true );
        //verificando se o indice ja existe ( icase mode )
        $exist = false;
        foreach( $json as $word => $tags ){
            if( strtolower( $_POST['word'] ) == strtolower( $word ) ){
                $json[ $word ] = $_POST['tags'];
                $exist = true;
                break;
            }
        }
        
        if( !$exist ){
            $json[ $_POST['word'] ] = $_POST['tags']; //adiciona a chave e o valor no json
        }
        
        $json = json_encode( $json );
        update_option("bbit_json", $json ); //salva o json no banco
        //oi, simples assim.
		?> <div class="updated"><p>Adicionado com sucesso.</p></div> <?php
     }
	?>
	<div class="wrap">
		<div style="float: left; width: 45%; padding-right: 2%; margin-right: 2%; border-right: solid 1px #CCCCCC">
			<h2>Op&ccedil;&otilde;es Boo-Box It!</h2>
			<form method="post">
				<p>Escolha seu programa de afiliado e insira seu c&oacute;digo de identifica&ccedil;&atilde;o.</p>
				<p>Se tiver d&uacute;vidas, entre em contato com a equipe boo-box em: <strong>contact@boo-box.com</strong></p>
				<fieldset class="options">
				<?php if (!get_option('bbit_affiliated')) { ?><div class="updated">Antes de mais nada, preencha com suas informa&ccedil;&otilde;es</div><?php } ?>
					<table>
						<tr>
							<td><p><strong>Programa:</strong></p></td>
							<td>
								<select name="bbit_affiliated" id="bbit_affiliated">
									<option <?php if(get_option('bbit_affiliated') == 'buscapeid') { echo 'selected'; } ?> value="buscapeid">Buscape</option>
									<option <?php if(get_option('bbit_affiliated') == 'mercadolivreid') { echo 'selected'; } ?> value="mercadolivreid">Mercado Livre</option>
									<option <?php if(get_option('bbit_affiliated') == 'submarinoid') { echo 'selected'; } ?> value="submarinoid">Submarino</option>
									<option <?php if(get_option('bbit_affiliated') == 'americanasid') { echo 'selected'; } ?> value="americanasid">Americanas.com</option>
									<option <?php if(get_option('bbit_affiliated') == 'jacoteiid') { echo 'selected'; } ?> value="jacoteiid">JaCotei</option>
									<option <?php if(get_option('bbit_affiliated') == 'amazon') { echo 'selected'; } ?> value="Amazon">Amazon</option>
									<option <?php if(get_option('bbit_affiliated') == 'ebayid') { echo 'selected'; } ?> value="eBayID">eBay</option>
									<option <?php if(get_option('bbit_affiliated') == 'shoppingcomid') { echo 'selected'; } ?> value="shoppingcomid">Shopping.com</option>
									<option <?php if(get_option('bbit_affiliated') == 'uolid') { echo 'selected'; } ?> value="UolID">Shopping UOL</option>
								</select>
							</td>
						</tr>
						<tr>
							<td><p><strong><label for="tag">ID</strong> (c&oacute;digo de traqueamento):</label></p></td>
							<td><input name="bbit_id" type="text" id="bbit_id" value="<?php echo get_option('bbit_id'); ?>" size="15" /></p>
						</tr>
						<tr>
							<td><p><strong><label for="tag">Ativar Boo-Box It!</strong>:</label></p></td>
							<td><input name="bbit_ligado" type="checkbox" 
							<?php  if(get_option('bbit_ligado') == 'on') { echo 'CHECKED=\"checked\"'; }
							
							?>
							
							/>
						</tr>
						<tr>
							<td><p><strong><label for="tag">N&atilde;o exibir links iguais no mesmo post</strong>:</label></p></td>
							<td><input name="bbit_once" type="checkbox" 
							<?php  if(get_option('bbit_once') == 'on') { echo 'CHECKED=\"checked\"'; }
							
							?>
							
							/>
						</tr>
					</table>
				</fieldset>
				<p><div class="submit"><input type="submit" name="update_bbit" value="Salvar Op&ccedil;&otilde;es &raquo;"  style="font-weight:bold;" /></div></p>
			</form>
			</div>
		
			<div style="float: left; width: 50%;">
				<h2>Lista de Palavras</h2>
					<p>Adicionar Palavras.</p>
					<p>Toda palavra ou termo adicionado na lista ser&aacute; substitu&iacute;do automaticamente por um link boo-box com as respectivas tags na hora de exibir seus posts. </p><p>Ps: Este plugin n&aacute;o edita seus posts.</p>
					<fieldset class="options">
						<table>
							<tr>
								<td><p><strong>Palavra ou Termo</strong> <small>Ex: <em>iphone</em></small></p></td>
								<td><p><strong>Tags</strong> <small>Ex: <em>smartphone apple mac</em></small></p></td>
							</tr>
							<?php
							//pegando o json no banco
							$obbits = json_decode( get_option('bbit_json'), true);

							if( count($obbits) ) foreach ($obbits as $word => $tags) {
								?>
							<tr>
								<td>
									<?php echo $word ?>
								</td>
								<td>
									<?php echo $tags ?>
								</td>
								<td>
									<form method="post"><input type="hidden" name="bbit_word" value="<?php echo $word ?>" /><input type="submit" name="excluir" value="excluir" /></form>
								</td>
							</tr>
							<?php
							}
							?>
							<tr>
							<td> &nbsp;</td></tr>
						<form method="post">
						
							<tr>
								<td width="200px">
									<input type="text" name="word" size="14" />
								</td>
							<td>
									<input type="text" name="tags" size="20" />
								</td>
							<td>
								<div class="submit"><input type="submit" name="addwords_bbit" value="Adicionar &raquo;"  style="font-weight:bold;" /></div>
							</td>
							</tr>
						</table>
					</fieldset>
					</form>
		</div>
		<div style="clear: both"></div>
	</div>
<?php } 

register_activation_hook(__FILE__,'bbit_install');
add_action('wp_head', 'bbit_add_boo_head');
add_action('admin_menu', 'bbit_add_options');
add_filter('the_content', 'bbit_replace');
add_filter('the_content_rss', 'bbit_replace');
?>
