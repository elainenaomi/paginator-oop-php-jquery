<?php 

/**
 * 
 * Enter description here ...
 * @author Elaine
 *
 */

class Navegacao
{
	private $Filtros; // filtros pro header
	private $TotalRegistros;
	private $TotalRegPagina; // total de registros por pagina
	private $TotalPaginas; //total de paginas baseados no total de registros e total de registros por pagina
	
	private $ItemRegPagina; //select (combobox)
	private $PagAtual;
	private $RegIniAtual;
	
	/** Parametros pra configurar a lista a ser paginada**/
	private $URLLista;
	private $URLParamLista;
	private $IDLista;
	
	private $URLAtualizaPaginador;
	private $URLParamAtualizaPaginador;
	private $URLLoadingImg;
	private $URLMudaPagina;
	private $URLParamMudaPagina;
	
	
	public function __construct($total = 60)
	{
		
		$this->TotalRegPagina = $total;
		$this->RegIniAtual = 0;
		
		
		$this->URLLista = "";
		$this->URLParamLista = "";
		$this->IDLista = "";
		$this->URLAtualizaPaginador = "";
		$this->URLParamAtualizaPaginador = "";
		$this->URLLoadingImg = "images/loading.gif";
		$this->URLMudaPagina = "";
		$this->URLParamMudaPagina = "";

	}


	/**
	 * 
	 * Adicionar um item para o header da tabela
	 * @param string $tipo text or select
	 * @param string $nome Nome do campo
	 * @param string $descricao
	 * @param string $valor
	 * @param array $items para o caso de select
	 */
	public function AdicionaItem($tipo, $nome, $descricao, $valor, $width, $itens = "", $tamanho_texto = "")
	{
		$tamanho = count($this->Filtros);
		
		$this->Filtros[$tamanho]['descricao'] = $descricao;
		$this->Filtros[$tamanho]['nome'] = $nome;
		$this->Filtros[$tamanho]['valor'] = $valor;
		$this->Filtros[$tamanho]['tipo'] = $tipo;
		$this->Filtros[$tamanho]['tamanho'] = $width;
		$this->Filtros[$tamanho]['itens'] = $itens;
		$this->Filtros[$tamanho]['tamanho_texto'] = $tamanho_texto;
	}
	
	
	/**
	 * Adiciona opções que irao aparecer no combo para numero de registros por pagina
	 * @param int $valor
	 */
	public function AdicionaItemRegPorPagina($valor)
	{
		$this->ItemRegPagina[$valor] = $valor;
	}
	
	/**
	 * Seleciona um valor de itens por pagina como padrao
	 * @param int $regPag
	 */
	public function AdicionaRegPagPadrao($regPag)
	{
		$this->TotalRegPagina = $regPag;
		
	}
	
	public function Header()
	{
		$retorno['header'] = $this->FiltroHeader();
		$retorno['action'] = $this->ActionFiltros();
		$retorno['navbar'] = $this->NavegacaoForm();
		
		return $retorno;
	}
	
	/**
	 * 
	 * Configuração do filtro para atualização da lista
	 * @param string $URLLista
	 * @param string $URLParamLista
	 * @param string $URLAtualizaPaginador
	 * @param string $URLParamAtualizaPaginador
	 */
	public function ConfiguraFiltro($URLLista, $URLParamLista, $IDLista, $URLAtualizaPaginador, $URLParamAtualizaPaginador, $URLMudaPagina, $URLParamMudaPagina)
	{
		
		$this->URLLista = $URLLista;
		$this->URLParamLista = $URLParamLista;
		$this->IDLista = $IDLista;
		$this->URLAtualizaPaginador = $URLAtualizaPaginador;
		$this->URLParamAtualizaPaginador = $URLParamAtualizaPaginador;
		$this->URLMudaPagina = $URLMudaPagina;
		$this->URLParamMudaPagina = $URLParamMudaPagina;
	}
	
	public function FiltroHeader()
	{
		if(is_array($this->Filtros))
		{
			
			$retorno['Filtros'] = $this->Filtros;
			
			ob_start();
			include_once "html/filter.php";
			$html = ob_get_contents();
			ob_end_clean();
			
			
			return $html;
			
		}
		
	}
	
	private function AjaxDataFiltro($dataLista = "", $order = false)
	{
		if(empty($dataLista)) $dataLista = $this->URLParamLista;
		
		if(is_array($this->Filtros))
		{
			if(!empty($dataLista)) $dataLista .= "&";

			/**
			 * Captura o conteudo dos filtros adicionados
			 */
			foreach($this->Filtros as $key => $value)
			{
				
				switch ($value['tipo']) {
					case 'select': 
						$dataLista .= $value['nome'].'="+$("select#'.$value['nome'].' option:selected").val()+"&';
					break;
					
					case 'text':
					
						$dataLista .= $value['nome'].'="+$("#'.$value['nome'].'").val()+"&';
					break;	
					default:
					break;
				}
					
			}
			
			$dataLista = substr($dataLista, 0, strlen($dataLista)-3);
			
			
			
			if($order == true)
			{
				/** Captura  a ordem da lista**/
				$dataLista .='+"&order="+id_order+"&tipo_order="+tipo_order';
			}
			
			$dataLista .='+"&NavLimit="+$("select#NavLimit option:selected").val()+"&RegistroInicial="+$("#RegistroInicial").val()';
			
			
		}
		
		return $dataLista;
	}
	
	private function ScriptFiltro($dataLista)
	{
		$dataAtualizaPaginador = $this->AjaxDataFiltro($this->URLParamAtualizaPaginador);
	
		
		$ajax_js = '

	      $.ajax({
	         url: "'.$this->URLAtualizaPaginador.'",
			 data: "'.$dataAtualizaPaginador.',
	         dataType: "json", 
	         success: function(json){ 
         
		 		var retornoLista;
		 
				retornoLista =  
					$.ajax({
					  type: "POST",
					  url: "'.$this->URLLista.'", 
					  data: "'.$dataLista.',
					  async: false,
					  success: function(data)
					  {
					
					  }
					  
					}).responseText;

				$("#TotalRegistros").text(json.TotalRegistros);		
				$("#TotalPaginas").text(json.TotalPaginas);
				$("#PaginaAtual").val(1);	
				$("#RegistroInicial").val(0);			
            	$("#'.$this->IDLista .'").html(retornoLista);
          
            	
            	
            	
         	}
      	});
      	
      	
      		$("#'.$this->IDLista.'").html("");
			'.$this->LoadingImg().'	
		
      	';
		
		return $ajax_js;
		
	}
	
	private function LoadingImg()
	{
		return '	var loading = $(
      						"<img id=loading alt=Carregando title=Carregando src=\"'.$this->URLLoadingImg.' \" /> "
						).appendTo("#'.$this->IDLista.'").show();
				
		loading.ajaxStart(function(){$(this).show();});
		loading.ajaxStop(function(){$(this).hide(); });';
		
	}
	
	public function ActionFiltros()
	{
		$dataLista = $this->AjaxDataFiltro();
		$dataOrder = $this->AjaxDataFiltro("",true);
		
		$js = '<script>
				$(function() {

					$(".filtro_texto").keypress(function(event) {
						if ( event.which == 13 ) {
						
							'.$this->ScriptFiltro($dataLista).'
					    }
					});
					
					$(".filtro_combobox").change(function() {
							'.$this->ScriptFiltro($dataLista).'
					});
					
					
						
					$(".filtro_order").click(function()
					{

						
						var tipo_order;
						var id_order = $(this).attr("for")
						
						if($(this).hasClass("order_asc")) 
						{
							if($(this).removeClass("order_asc"))
							{
								$(this).children().removeClass("ui-icon-triangle-1-n");
								$(this).children().addClass("ui-icon-triangle-1-s");
								
								$(this).addClass("order_desc");
								
							}	
							tipo_order = "desc";
								
						}
						else if($(this).hasClass("order_desc"))
						{
							if($(this).removeClass("order_desc"))
							{
								$(this).children().removeClass("ui-icon-triangle-1-s");
								$(this).children().addClass("ui-icon-triangle-1-n");
								
								$(this).addClass("order_asc");
								
							}	
							
							tipo_order = "asc";
						
						}
						else
						{
							$(this).addClass("order_asc");
							$(this).children().removeClass("ui-icon-triangle-2-n-s");
							$(this).children().addClass("ui-icon-triangle-1-n");
							
							tipo_order = "asc";
							
						}
						
						
						$(".filtro_order").each(function(index) 
						{
							if(id_order != $(this).attr("for"))
							{
								$(this).children().removeClass("ui-icon-triangle-1-s");
								$(this).children().removeClass("ui-icon-triangle-1-n");
								$(this).children().addClass("ui-icon-triangle-2-n-s");
							
								$(this).removeClass("order_asc");
								$(this).removeClass("order_desc");
							}
							
						});
						
						'.$this->ScriptFiltro($dataOrder).'
					});
					
				});
				</script>';
		
		return $js;
	}
	
	public function NavegacaoForm()
	{
		$retorno['TotalRegistros'] = 0;
		$retorno['PagAtual'] = 1;
		$retorno['ItemRegPagina'] = $this->ItemRegPagina;
		$retorno['TotalRegPagina'] = $this->TotalRegPagina;
		$retorno['TotalPaginas'] = 0;
		$retorno['RegistroInicial'] = 0;
		
		
		$retorno['Script'] = $this->ActionNavBar();
		
		
		ob_start();
		include_once "html/pagNav.php";
		$html = ob_get_contents();
		ob_end_clean();
	
	
	
		return $html;
	}
	
	public function ActionNavBar()
	{
		$dataNavLimit = $this->AjaxDataFiltro();
		$dataPaginaAtual  = $this->AjaxDataNavBar("PaginaAtual");
		
		$js ='<script>
					$(function() {
					
						$("#NavLimit").change(function() {

							'.$this->ScriptFiltro($dataNavLimit).'
						
						
						});
						
						
						$("#PaginaAtual").keypress(function(event) {
					
					    	if ( event.which == 13 ) {
				    	
					    		'.$this->AjaxScriptMudaPagina($dataPaginaAtual,'PaginaAtual').'
					    	
					    	}
					    });
					    
					    $(".MudaPagina").click(function(){
							
							var id = $(this).attr("id");
							
							var asc = $(".order_asc").length;
							var desc = $(".order_desc").length;
							
							
							if(asc == 1)
							{
							
								$(".order_asc").each(function(index) 
								{
									id_order = $(this).attr("for");
									tipo_order = "asc";									
									
								});
								
							}
							else if(desc == 1)
							{
								$(".order_desc").each(function(index) 
								{
									id_order = $(this).attr("for");
									tipo_order = "desc";									
									
								});
							
							}
							else
							{
								id_order = "";
								tipo_order = "";
							}
							
							
							
						
							'.$this->AjaxScriptMudaPagina($dataPaginaAtual,'MudaPagina').'
						
		
						});
					    
					});
	
					</script>';
			
		return $js;
		
	}
	
	private function AjaxScriptMudaPagina($dataLista, $tipoComando)
	{
		
		if($tipoComando == "PaginaAtual") $Tipo = true;
		else $Tipo = false;
		
		$dataListaMudaPagina = $this->AjaxDataNavBar("ListaPaginaAtual",$Tipo);
		$dataMudaPagina = $this->AjaxDataNavBar($tipoComando);
	
		
		$ajax_js = '
		      
	      $.ajax({
	         url: "'.$this->URLMudaPagina.'",
			 data: "'.$dataMudaPagina.',
	         dataType: "json", 
	         success: function(json){ 
         
		 		var retornoLista;
		 

		 		
				
				if(json.TotalRegistros > 0)
				{
					$("#PaginaAtual").val(json.PaginaAtual);	
					$("#RegistroInicial").val(json.RegistroInicial);			
            		
            		
            		retornoLista =  
					$.ajax({
					  type: "POST",
					  url: "'.$this->URLLista.'", 
					  data: "'.$dataListaMudaPagina.',
					  async: false
					  
					}).responseText;
					
					$("#'.$this->IDLista .'").html(retornoLista);
          		}
          		else
          		{
          			$("#'.$this->IDLista.'").html("");
          		}
         	}
      	});
      	
      	
      	$("#'.$this->IDLista.'").html("");
      	
      	'.$this->LoadingImg().'	
		
					
					
      	
      	';
		
		return $ajax_js;
		
	}
	
	
	
	
	
	
	private function AjaxDataNavBar($Comando = "", $PaginaAtual = false)
	{
		
		
		
		if($Comando == "PaginaAtual")
		{
			$dataLista = $this->AjaxDataFiltro($this->URLParamMudaPagina, true);	
			$dataLista .='+"&PaginaAtual="+$("#PaginaAtual").val()+"&TotalRegistros="+$("#TotalRegistros").text()';
		}
		else if($Comando == "ListaPaginaAtual")
		{
			$dataLista = $this->AjaxDataFiltro("",true);
			$dataLista .='+"&PaginaAtual="+$("#PaginaAtual").val()+"&TotalRegistros="+$("#TotalRegistros").text()';
			
			if($PaginaAtual == false)
				$dataLista .= '+"&MudaPagina="+$(this).attr("id")';
		}
		else if($Comando == "MudaPagina")
		{
			$dataLista = $this->AjaxDataFiltro($this->URLParamMudaPagina,true);	
			$dataLista .='+"&PaginaAtual="+$("#PaginaAtual").val()+"&TotalRegistros="+$("#TotalRegistros").text()';
			$dataLista .= '+"&MudaPagina="+$(this).attr("id")';
		}
		
		
		
		
		
		return $dataLista;
		
	}
	
	
	

	
	public function DeterminaRegistroInicialAtual($mudaPag, $numPag = "")
	{
		
		
		switch ($mudaPag)
		{
			case 'fimPag':
				
				if($this->TotalPaginas > 1)
					$this->RegIniAtual =  ($this->TotalPaginas -1) * $this->TotalRegPagina;
				
				$this->PagAtual = $this->TotalPaginas;
					
				break;
				
			case 'antPag':
				
				$this->RegIniAtual -= $this->TotalRegPagina;
				
				
				if($this->RegIniAtual <= 0) 
				{
					$this->RegIniAtual = 0;
					$this->PagAtual = 1;
				}
				else 
				{
				
					$this->PagAtual--;
				}
				
				
				break;	
				
			case 'proxPag':
				
				$this->RegIniAtual += $this->TotalRegPagina;
				
				
				if($this->RegIniAtual >= (($this->TotalPaginas -1) * $this->TotalRegPagina)) 
				{
					$this->RegIniAtual =  ($this->TotalPaginas -1) * $this->TotalRegPagina;
					 $this->PagAtual = $this->TotalPaginas;
				}
				else
				{
					$this->PagAtual++;
				}
					
					
				break;	
					
				
			case 'PaginaAtual':	
				
				if(!empty($this->PagAtual))
				{

					if($this->PagAtual <= 0)
					{
						$this->PagAtual =  1;
						$this->RegIniAtual =  0;
					}
					else if($this->PagAtual >= $this->TotalPaginas)
					{
						$this->PagAtual = $this->TotalPaginas;
						$this->RegIniAtual =   ($this->PagAtual -1) * $this->TotalRegPagina;
					}
					else 
					{
						$this->RegIniAtual =  ($this->PagAtual -1) * $this->TotalRegPagina;
					}
				}
				else 
				{
					$this->PagAtual = 1;
					$this->RegIniAtual =  0;
				}
				
				
				break;
				
			case "iniPag":
			default:
				$this->PagAtual = 1;
				$this->RegIniAtual =  0;
				break;
		}
		

		
		
	}
	public function RegistroInicialAtual()
	{
		return $this->RegIniAtual;
	}
	
	public function RegistroPaginaAtual()
	{
		return $this->PagAtual;
	}
	
	public function AtualizaPaginador($totalReg, $totalRegPag, $mudaPag,$RegIniAtual, $pagAtual  = "")
	{
		$this->TotalRegistros = $totalReg;
		$this->TotalRegPagina = $totalRegPag;
		$this->RegIniAtual = $RegIniAtual;
		
		$this->CalculaTotalPagina();
		$this->DeterminaRegistroInicialAtual($mudaPag, $pagAtual );
		
		
		
	}
	
	private function CalculaTotalPagina()
	{
		if($this->TotalRegPagina > 0)
			$this->TotalPaginas =  ceil ( $this->TotalRegistros/$this->TotalRegPagina);
		else $this->TotalPaginas = 1;
		
		
	}
	
	public function TotalPaginas($TotalReg, $TotalRegPag)
	{
		$this->TotalRegistros = $TotalReg;
		$this->TotalRegPagina = $TotalRegPag;
		$this->CalculaTotalPagina();
		
		return $this->TotalPaginas;
		
	}
	
	public function AtualizaNavegacao($PaginaAtual,$TotalReg, $TotalRegPag,$RegistroInicial = 0, $ComandoNav)
	{
		if(empty($ComandoNav)) $ComandoNav = "PaginaAtual";
		
		
	
		$this->PagAtual = $PaginaAtual;
		$this->TotalRegistros = $TotalReg;
		$this->TotalRegPagina = $TotalRegPag;
		$this->RegIniAtual = $RegistroInicial;
		
		$this->CalculaTotalPagina();
		
		$this->DeterminaRegistroInicialAtual($ComandoNav);
		
		$retorno["RegistroInicial"]  = $this->RegIniAtual;
		$retorno['PaginaAtual'] = $this->PagAtual;
		
		return $retorno;
	}
	
	
	
	
	
	
}


?>