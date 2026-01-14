<?php
	foreach( $Includes as $File )
	{
		echo '<h4 class="file"><a data-file="' . $File . '" href="' . $BaseURL . $File . '">' . $File . '</a></h4>';
		echo '<div class="nav-functions ' . ( $CurrentOpenFile === $File ? ' show' : '' ) . '" id="file-' . $File . '">';
		
		if( !empty( $Functions[ $File ] ) )
		{
			$PreviousFunctionType = 'hypehypehype';
			$OpenList = false;
			
			foreach( $Functions[ $File ] as $Function )
			{
				if( $PreviousFunctionType !== $Function[ 'Type' ] )
				{
					$PreviousFunctionType = $Function[ 'Type' ];
					
					if( $OpenList )
					{
						echo '</ul></div></div>';
					}
					
					$OpenList = true;
					
					echo GetFunctionHeader( $Function[ 'Type' ] ) . '<div class="card-body"><ul class="nav nav-sidebar">';
				}
				
				$FunctionName = htmlspecialchars( $Function[ 'Function' ] );
				
				echo '<li class="function' . ( $CurrentOpenFunction === $FunctionName ? ' active' : '' ) . '" data-title="' . $FunctionName. '" data-content="' . htmlspecialchars( $Function[ 'Comment' ] ) . '">';
				echo '<a href="' . $BaseURL . $File . '/' . urlencode( $Function[ 'Function' ] ) . '">' . $FunctionName . '</a>';
				echo '</li>';
			}
			
			if( $OpenList )
			{
				echo '</ul></div></div>';
			}
		}
		else
		{
			echo '<div class="card border-primary mb-2"><div class="card-header bg-primary text-white">No functions</div><div class="card-body text-center">This include file has no functions.</div></div>';
		}
		
		echo '</div>';
	}
	
	function GetFunctionHeader( $Type )
	{
		switch( $Type )
		{
			case 'forward': return '<div class="card border-info mb-2"><div class="card-header bg-info text-white">Forwards</div>';
			case 'native': return '<div class="card border-success mb-2"><div class="card-header bg-success text-white">Natives</div>';
			case 'stock': return '<div class="card border-warning mb-2"><div class="card-header bg-warning">Stocks</div>';
			case 'functag': return '<div class="card border-danger mb-2"><div class="card-header bg-danger text-white">Functags</div>';
		}
		
		return '<div class="card border-primary mb-2"><div class="card-header bg-primary text-white">' . $Type . '</div>';
	}
