<?php
	require "../db_connect.php";
	require "../message_display.php";
	require "verify_member.php";
	require "header_member.php";
?>

<html>
	<head>
		<title>Mis Libros</title>
		<link rel="stylesheet" type="text/css" href="../css/global_styles.css">
		<link rel="stylesheet" type="text/css" href="../css/custom_checkbox_style.css">
		<link rel="stylesheet" type="text/css" href="css/my_books_style.css">
	</head>
	<body>
	
		<?php
			$query = $con->prepare("SELECT book_isbn FROM book_issue_log WHERE member = ?;");
			$query->bind_param("s", $_SESSION['username']);
			$query->execute();
			$result = $query->get_result();
			$rows = mysqli_num_rows($result);
			if($rows == 0)
				echo "<h2 align='center'>No hay libros solicitados actualmente</h2>";
			else
			{
				echo "<form class='cd-form' method='POST' action='#'>";
				echo "<legend>Mis Libros</legend>";
				echo "<div class='success-message' id='success-message'>
						<p id='success'></p>
					</div>";
				echo "<div class='error-message' id='error-message'>
						<p id='error'></p>
					</div>";
				echo"<table width='100%' cellpadding='10' cellspacing='10'>
						<tr>
							<th></th>
							<th>ISBN<hr></th>
							<th>Título<hr></th>
							<th>Autor<hr></th>
							<th>Categoría<hr></th>
							<th>Fecha de vencimiento<hr></th>
						</tr>";
				for($i=0; $i<$rows; $i++)
				{
					$isbn = mysqli_fetch_array($result)[0];
					if($isbn != NULL)
					{
						$query = $con->prepare("SELECT title, author, category FROM book WHERE isbn = ?;");
						$query->bind_param("s", $isbn);
						$query->execute();
						$innerRow = mysqli_fetch_array($query->get_result());
						echo "<tr>
								<td>
									<label class='control control--checkbox'>
										<input type='checkbox' name='cb_book".$i."' value='".$isbn."'>
										<div class='control__indicator'></div>
									</label>
								</td>";
						echo "<td>".$isbn."</td>";
						for($j=0; $j<3; $j++)
							echo "<td>".$innerRow[$j]."</td>";
						$query = $con->prepare("SELECT due_date FROM book_issue_log WHERE member = ? AND book_isbn = ?;");
						$query->bind_param("ss", $_SESSION['username'], $isbn);
						$query->execute();
						echo "<td>".mysqli_fetch_array($query->get_result())[0]."</td>";
						echo "</tr>";
					}
				}
				echo "</table><br />";
				echo "<input type='submit' name='b_return' value='Devolver los libros seleccionados' />";
				echo "</form>";
			}
			
			if(isset($_POST['b_return']))
			{
				$books = 0;
				for($i=0; $i<$rows; $i++)
					if(isset($_POST['cb_book'.$i]))
					{
						$query = $con->prepare("SELECT due_date FROM book_issue_log WHERE member = ? AND book_isbn = ?;");
						$query->bind_param("ss", $_SESSION['username'], $_POST['cb_book'.$i]);
						$query->execute();
						$due_date = mysqli_fetch_array($query->get_result())[0];
						
						$query = $con->prepare("SELECT DATEDIFF(CURRENT_DATE, ?);");
						$query->bind_param("s", $due_date);
						$query->execute();
						$days = (int)mysqli_fetch_array($query->get_result())[0];
						
						$query = $con->prepare("DELETE FROM book_issue_log WHERE member = ? AND book_isbn = ?;");
						$query->bind_param("ss", $_SESSION['username'], $_POST['cb_book'.$i]);
						if(!$query->execute())
							die(error_without_field("ERROR: No pude devolver los libros"));
						
						if($days > 0)
						{
							$penalty = 5*$days;
							$query = $con->prepare("SELECT price FROM book WHERE isbn = ?;");
							$query->bind_param("s", $_POST['cb_book'.$i]);
							$query->execute();
							$price = mysqli_fetch_array($query->get_result())[0];
							if($price < $penalty)
								$penalty = $price;
							$query = $con->prepare("UPDATE member SET balance = balance - ? WHERE username = ?;");
							$query->bind_param("ds", $penalty, $_SESSION['username']);
							$query->execute();
							echo '<script>
									document.getElementById("error").innerHTML += "Un penalty de $ '.$penalty.' fue impuesto por retener tu libro '.$_POST['cb_book'.$i].' por '.$days.' días después del tiempo convenido de entrega.<br />";
									document.getElementById("error-message").style.display = "block";
								</script>';
						}
						$books++;
					}
				if($books > 0)
				{
					echo '<script>
							document.getElementById("success").innerHTML = "Proceso de devolución exitoso '.$books.' libros";
							document.getElementById("success-message").style.display = "block";
						</script>';
					$query = $con->prepare("SELECT balance FROM member WHERE username = ?;");
					$query->bind_param("s", $_SESSION['username']);
					$query->execute();
					
					$balance = (int)mysqli_fetch_array($query->get_result())[0];
					if($balance < 0)
						header("Location: ../logout.php");
				}
				else
					echo error_without_field("Por favor seleccione el libro a devolver");
			}
		?>
		
	</body>
</html>