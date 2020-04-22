<?php
	require_once('connectDB.php');
	require __DIR__ . '../../../vendor/autoload.php';
	use Minishlink\WebPush\WebPush;
	use Minishlink\WebPush\Subscription;
	
	function pushSubscriber($bodyMessage) {
		$post = json_decode(file_get_contents('php://input'));
        $db = new ConnectionDB();
        $conn = $db->getConnection();

	    $state = $conn->prepare('SELECT * FROM usuariosuscripcion');
	    $state->execute(); 
	    $result = $state->get_result();

	    $notifications = (array) [];

        if ($result->num_rows > 0) { //hay usuarios suscriptos a estas alertas

        	$auth = [
			    'VAPID' => [
			        'subject' => 'yomanejoserver@gmail.com',
			        'publicKey' => "BGl7F8lkZqntl6jPBuFdMxk64eKKL4NZKGZg0sneZ6uoWo1S0FqdRL1bRQFrTd3df4v4a2GTEKnKgsSaMf44oc4", 
			        'privateKey' => "4fshUb_IaCqFeQhHYiyl-EHHwqslcVc47FnbaXNbMQU", 
			    ],
			];

   			$webPush = new WebPush($auth);

            while($row = $result->fetch_assoc()) {
                $endpoint = $row["endpoint"];
				$p256dh = $row["p256dh"];
				$auth = $row["auth"];

				$notification = 
				[
			        'subscription' => Subscription::create([
			            "endpoint" => $endpoint,
	              		"keys" => [
	                  		'p256dh' => $p256dh,
	                  		'auth' => $auth
	           		 	],
			        ]),
				    'payload' => '{ "notification": {"title": "Cronograma finalizado !", "body":"'.$bodyMessage.'", "icon":"assets/img/logo.PNG",  "badge":"assets/img/logo.PNG", "vibrate": "[100, 50, 100]" } }',
		    	];

			    $webPush->sendNotification(
			        $notification['subscription'],
			        $notification['payload'],
			        true
		    	);

		    	/**
				* Check sent results
				* @var MessageSentReport $report
				*/
				foreach ($webPush->flush() as $report) {
		    		$endpoint = $report->getRequest()->getUri()->__toString();

		    		if ($report->isSuccess()) {
				        echo json_encode("[v] Message sent successfully for subscription {$endpoint}.");
				    } else {
				        echo "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
				    }
				}
            }
        }
		mysqli_close($conn);
	}

	function checkIfScheduleIsCompleted() {
        $db = new ConnectionDB();
        $conn = $db->getConnection();

    	$status = "FINALIZADO";
	    $state = $conn->prepare('SELECT * FROM cronograma WHERE DATE_FORMAT(cronograma.timestampFinalizado, "%Y-%m-%d %H %i") = DATE_FORMAT(NOW(), "%Y-%m-%d %H %i") and cronograma.status = ?');
	    $state->bind_param('s', $status);
	    $state->execute(); 
	    $result = $state->get_result();

        if ($result->num_rows > 0) { //acaba de finalizar un cronograma. Envio PUSH notification
        	$bodyMessage = "";
        	if ($result->num_rows > 1) {
        		$bodyMessage = "Algunos cronogramas acaban de finalizar";
        	} else {
        		$idCronograma;
        		while($row = $result->fetch_assoc()) {
        			$idCronograma = $row['idCronograma'];
        		}
        		$bodyMessage = "El cronograma ".$idCronograma." acaba de finalizar";
        	}
        	pushSubscriber($bodyMessage);
        }
       	mysqli_close($conn);
	}

	checkIfScheduleIsCompleted();
?>