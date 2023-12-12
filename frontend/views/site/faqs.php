<?php

use yii\helpers\Url;
use yii\helpers\Html;
use frontend\widgets\FaqWidget;
$this->title = Yii::t('frontend', 'FAQs');


?>

<div class="site-faqs">
    <div class="container">
		<?php 
			$label = [
				"Are the trails accessible at all time?",
				"Who can download and use the app?",
				"Do I need to pay for the app? ",
				"Are there in-app purchases?",
				"Where can I report an error or provide feedback with regard to the app?",
				"Where can I submit my feedback to NParks that are non-related to the app?",
				"Is the C2C Trail accessible by bicycles?"
			];

			$content = [
				"The C2C Trail is accessible at all times except for certain parks and gardens which are closed at night. For example, some Nature Parks are closed from 7pm to 7am, while the Singapore Botanic Gardens is closed from midnight to 5am. These hours will apply during any special events period as well. Participants can check the opening hours of parks and gardens from the NParks website.",
				"The mobile app is available for download and use by anyone in Singapore.",
				"The app is free to use and no payment is required.",
				"There are no in-app purchase options in the app.",
				"You can submit a report via the in-app feature at Me->Settings->Report a Problem.",
				"You can send feedback and suggestions at <a href=https://www.nparks.gov.sg/feedback>https://www.nparks.gov.sg/feedback</a>.",
				"You are able to access the Trail by bicycles however there are certain stretches that require you to dismount and push. For the full experience of the Trail, we highly encourage you to explore on foot.",
			];
			echo FaqWidget::widget(['header' => 'General', "label" => $label, "content" => $content]); 



			$label = [
				"Which phones can run the app at an optimal level?",
				"Will the app run in the background and drain my phone’s battery?",
				"Will I receive inbox notifications when I am not using the app?",
				"What happens if I have no network coverage?",
				"Does the GPS function in my phone need to be enabled the entire time I am using the app?",
				"What happens when I exit the C2C Trail with the app still running?",
				"Why did I receive an error message where access to the app will be limited?"
			];

			$content = [
				"The app is available for mobile phones which use either the Android operating system (OS) or Apple’s iOS. For those on Android OS, the minimum required version is Android version 6.0 and above (API 22). For those on iOS, the minimum required version is iOS 10.3 and above.",
				"The app will not run in the background. However, it will remain open until you actively close the app.",
				"Users will still receive push notifications in their inbox even when the app is not turned on.",
				"The app will not be able to function without WiFi or 4G/3G connection.",
				"The GPS function should be enabled to best enjoy the features of the app as many of the features (eg. navigation) are tagged to your location.",
				"You will be able to view your location on the homepage map and navigate back to the C2C Trail but the other features will not be available.",
				"As the system discovered irregularities with your mobile device, access to the app will be limited. We would encourage you to switch to another device to be able to gain full access to all the app features."
			];
			echo FaqWidget::widget(['header' => 'Technical', "label" => $label, "content" => $content]); 



			$label = [
				"How can I participate in Rewards programme?",
				"What are Rewards points?",
				"How do I earn Rewards points?",
				"How many points are given for each feature or function in the app?",
				"How do I receive the e-vouchers?",
				"How can I redeem my e-vouchers?",
				"What happens if I do not redeem my e-vouchers?",
				"Can I give, exchange or purchase the e-vouchers?",
				"Can someone else redeem the e-vouchers on my behalf with my phone?",
				"What can I do if I face further issues with Rewards?",
			];

			$content = [
				"As long as you are registered with the C2C mobile app, you are automatically a participant of the Rewards programme.",
				"Reward points are represented by “flowers” in the app and you can accumulate them to redeem gifts or e-vouchers.",
				"You can earn points by exploring the C2C Trail and using certain features and functions of the app, such as:
				<ul>
					<li>Daily visits to the Trail
					<li>Visiting checkpoints along the Trail
					<li>Completing quests at checkpoints
					<li>Searching for Hidden Fruits that may appear in randomised locations daily
					<li>Uploading and sharing photos
				</u>",
				"<ul>
					<li>Daily visits to the Trail – 10 points
					<li>Visiting checkpoints along the Trail – 0 points for the first checkpoint, 50 points for each subsequent checkpoint
					<li>Completing quests at checkpoints – 50 points per quest
					<li>Searching for Hidden Fruits that may appear in randomised locations daily – 10 points per fruit and 15 points per bonus fruits
					<li>Uploading and sharing photos – 5 points per post (up to 10 points daily)
				</ul>",
				"You will receive a notification via the app when you have accumulated 500 “flowers”. The notification will also include an e-voucher that you can redeem. 500 “flowers” will be deducted from your account thereafter. The current minimum number of “flowers” for e-vouchers redemption is for a limited time period only and may change without notice.",
				"There are two types of e-vouchers available. The first requires the user to head down to the store to redeem a physical gift or service, while the second is an online code for online stores or bookings.",
				"Each e-voucher has a limited redemption period. E-vouchers that are not redeemed within the valid redemption period will be void.",
				"E-vouchers are non-transferrable, non-exchangeable and not available for purchase.",
				"No. If you are the registrant of the app and owner of the mobile phone, you have to redeem your received e-vouchers yourself.",
				"You can write in to <a href=nparks_programmes_events@nparks.gov.sg>nparks_programmes_events@nparks.gov.sg</a> or submit a feedback via the app: Me->Settings->Report a Problem
",
			];
			echo FaqWidget::widget(['header' => 'Rewards', "label" => $label, "content" => $content]); 




			$label = [
				"How do I navigate to the C2C Trail?",
				"What if I do not have the OneMap app in my phone?",
				"Can I use the Coast-to-Coast Mobile Application as a navigation tool to other parts of Singapore?",
				"Can I use other navigation apps to travel to the C2C Trail?",
				"Why is there another route to Rower’s Bay? Is this part of the C2C Trail?",
			];

			$content = [
				"You may tap on any point or checkpoint along the Trail that you see on the mobile app and the app will activate OneMap to provide directions and instructions. ",
				"You will be re-directed to OneMap’s mobile webpage.",
				"You will not be able to use the app as a navigation tool to other parts of Singapore as the app is curated specifically for the C2C Trail.",
				"You may use other apps, however navigation along the trail may not be accurate. You will also not be able to enjoy the functions of the app, such as collecting Rewards points.",
				"The route is an offshoot of the C2C Trail to take users to the newly opened Round Island Route node at Rower’s Bay. The app may be extended to include other park connectors in the future.",
			];
			echo FaqWidget::widget(['header' => 'Navigation (Explore)', "label" => $label, "content" => $content]); 



			$label = [
				"Who can view my photos?",
				"How do I report inappropriate photos?",
				"Will this feature work if I have exited the C2C Trail?",
				"Can I remove my photos after I have uploaded them?",
				"How long will my photos be featured on the app?",
				"Can I share my photos onto my personal social media accounts?",
			];

			$content = [
				"Anyone who is registered with the app will be able to view the photos.",
				"You may flag out photos by tapping on the top right corner of the photo and clicking on 'Report Post'.",
				"It will not work as uploading and sharing of photos can only be used when you are along the Trail.",
				"You may delete them under the ‘Me’ tab by clicking on the photo and tapping on the ‘Delete’ option on the top right dropdown menu. ",
				"Your photos will remain in the app unless you delete them. ",
				"You may share your photos on your social media accounts.",
			];
			echo FaqWidget::widget(['header' => 'User-experience Sharing (Community)', "label" => $label, "content" => $content]); 





			$label = [
				"Where can I find Checkpoints?",
				"How do I know if I have arrived at a Checkpoint?",
				"Can I repeat the quests?",
				"How do I access the AR scanner?",
				"Why am I told to wait when I have arrived at the following Checkpoint from the previous one?",
				"Can I travel from one Checkpoint to another via other forms of transport?",
				"Do I have to leave the app open while I walk from one Checkpoint to the next?",
				"Are there signs to lead me to these checkpoints along the C2C Trail?",
			];

			$content = [
				"The Checkpoint locations are shown on the homepage map feature of the app.",
				"There is a physical signboard at each Checkpoint location and there are instructions on how to activate certain features of the app.",
				"You can repeat the quests as many times as you wish, however Reward points will only be awarded once every 24 hours.",
				"The AR scanner can be accessed via the ‘Explore’ tab. Click on the Camera icon on the top left, find the ‘scan’ icon on the top right and click to scan.",
				"The C2C Trail provides a curated walking experience and is best enjoyed on foot, as it may be dangerous to use the app while cycling or while riding personal mobility devices. To encourage you and other users to walk the C2C Trail using the app, the checkpoints and time restriction have been spread out accordingly based on average walking speed.",
				"You are encouraged to use the app to explore the C2C Trail on foot for the best experience.",
				"Your app should be left open so that you can find Hidden Fruits in order to gain more reward points.",
				"There will be C2C Trail signs and each Checkpoint will be demarcated with a specific sign as well. You can also use the app to navigate to the Checkpoints.",

			];
			echo FaqWidget::widget(['header' => 'Checkpoints', "label" => $label, "content" => $content]); 





			$label = [
				"What is this feature about?",
				"Does the app feature other Points of Interests in Singapore?",
			];

			$content = [
				"Places of Interests carry information about the attractions and tenants that are located along the Trail. You can find out more about these places and get directions via the app.",
				"The app only features Points of Interests that are found along the C2C Trail.",
			];
			echo FaqWidget::widget(['header' => 'Places of Interests (Information)', "label" => $label, "content" => $content]); 



			$label = [
				"What notifications will I receive?",
				"Can I send messages to other users?",
				"Can I unsubscribe or choose not to receive these notifications?",
			];

			$content = [
				"You may receive updates on upcoming events, F&B promotions, invitations to participate in special app events and more.",
				"You are not able to send messages to other users. ",
				"You are not able to disable any of the features within the app.",
			];
			echo FaqWidget::widget(['header' => 'Notifications', "label" => $label, "content" => $content]); 



			$label = [
				"What is this feature about?",
				"How do I participate in special events?",
				"Will there be announcements made for such special events?",
				"How long are the special events?",
				"Why are there special events?",
			];

			$content = [
				"The app will occasionally feature special events which all users can participate in. These special events may be in conjunction with festivities, national celebrations, and partner promotions.",
				"All registered users are eligible to participate in the special events.",
				"Announcements will be made via several channels such as the NParks Facebook page and in-app notifications.",
				"The duration of each special event may vary and will be shared when the event is announced.",
				"Special events provide you and other users with different ways of experiencing the C2C Trail. It is also a form of encouragement for you to visit our parks and green spaces more frequently and lead a more active lifestyle. ",
			];
			echo FaqWidget::widget(['header' => 'Special Events & Challenges', "label" => $label, "content" => $content]); 
















		?>

    </div>
</div>