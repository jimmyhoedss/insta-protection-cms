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
				"What is the NParks Coast-to-Coast trail?",
				"Are the trails accessible at all time?",
				"What is the Coast-to-Coast Mobile Application?",
				"Who can download and use the app?",
				"How can I download the app?",
				"Do I need to pay for the app? ",
				"Are there in-app purchases?",
				"When is the app available?",
				"How does the app work?",
				"Where can I report an error or provide feedback with regard to the app?",
				"Where can I submit my feedback to NParks that are non-related to the app?",
			];

			$content = [
				"The Coast-to-Coast trail is a 36km trail that spans across Singapore. Stretching from Jurong Lake Gardens in the west to Coney Island Park in the northeast, it will take users through a variety of parks, park connectors, nature areas, places of interest and urban spaces.",
				"The Coast-to-Coast trail is accessible to users at all times, however certain nature areas and gardens are closed at certain hours. Nature areas are closed from 7pm to 7am while the Singapore Botanic Gardens closes midnight onwards to 5am. We would advise users to find out the opening hours before entering parks and nature areas.",
				"It is an integrated walking navigational mobile app, providing a curated trail for visitors with the use of Augmented Reality technology to enhance users’ experience when exploring the Coast-to-Coast trail.",
				"The mobile app is available for download and use by all residents and visitors of Singapore.",
				"The app is available for download from Apple’s App Store and Google’s Play Store.",
				"The app is free to use and no payment is required.",
				"There are no in-app purchase options in the app.",
				"The app will be available from 26 January 2019 onwards.",
				"By registering with the app, users are able to use features of the app to enhance their experience when visiting the Coast-to-Coast trail. Users can utilise the navigation function to be directed to the Coast-to-Coast trail and subsequently activate interactive features along the way. Users are encouraged to walk the Coast-to-Coast trail that highlights points of interests along the way, AR characters, share photos in-app and on social media, receive updates and promotions, and gain reward points by doing all the above. ",
				"Users can submit a report via the in-app feature at Me->Settings->Report a Problem",
				"Users can send feedback and suggestions to nparks_programmes_events@nparks.gov.sg",
			];
			echo FaqWidget::widget(['header' => 'General', "label" => $label, "content" => $content]); 



			$label = [
				"Which phones can run the app at an optimal level?",
				"Will the app run in the background and drain my phone’s battery?",
				"Will I receive notifications when I am not using the app?",
				"What happens if I have no network coverage?",
				"Do I need to turn on the GPS function in my phone the whole time?",
				"What happens when I exit the Coast-to-Coast Trail with the app still running?",
			];

			$content = [
				"For Android phones, the minimum required version is Android version 5.1 and above (API 22). For IPhones, the minimum required version is iOS 10.3 and above.",
				"No it cannot run in the background and will not drain your phone's battery.",
				"Yes,you will stil receive notifications when the app is closed.",
				"You will be unable to see any posts in the app, upload photos, scan the AR models, see or redeem rewards and find hidden fruits.",
				"It is not necessary to have it turned on all the time, but it is recommended as most of the app functions requires the location of your phone.",
				"The app will continue to show your location on OneMap, nothing more.",
			];
			echo FaqWidget::widget(['header' => 'Technical', "label" => $label, "content" => $content]); 



			$label = [
				"What is the Rewards feature about?",
				"How can I participate in Rewards?",
				"What are Rewards points?",
				"How do I earn Rewards points?",
				"What do I stand to win?",
				"How do I receive the e-vouchers?",
				"How can I redeem my e-vouchers?",
				"What happens if I do not redeem my e-vouchers?",
				"Can I give, exchange or purchase the e-vouchers?",
				"Can someone else redeem the e-vouchers on behalf with my phone?",
				"What can I do if I face further issues with Rewards?",
			];

			$content = [
				"Users stand to gain Reward points as they visit the trail and activate features of the app. These points are redeemable against attractive gifts or discount vouchers.",
				"All users who are registered with the Coast-to-Coast app are automatically a participant of the Rewards program.",
				"The Rewards points are represented by Flowers in the app and the accumulation of Flowers allow users to redeem against e-vouchers.",
				"Users will need to visit the Coast-to-Coast trail with the app and carry out certain features and functions of the app, such as:
				<br>&nbsp;&nbsp;&nbsp;i. Daily logins
				<br>&nbsp;&nbsp;&nbsp;ii. Visiting checkpoints along the trail
				<br>&nbsp;&nbsp;&nbsp;iii. Complete quizzes, quests and challenges
				<br>&nbsp;&nbsp;&nbsp;iv. Search for Hidden Fruits that may appear in random locations daily
				<br>&nbsp;&nbsp;&nbsp;v. Upload and share photos",
				"Redeemable rewards are constantly changing as we continuously diversify the range of prizes and vouchers.",
				"Users will be sent with a notification via the app when they have accumulated 500 Flowers. The notification also includes an e-voucher that is redeemable by the user. 500 Flowers will be deducted from user’s account thereafter. Users should also note that the current minimum number of Flowers for e-vouchers redemption is for a limited time period only and may change without notice.",
				"Users will receive two types of e-vouchers. The first requires a user to head down to the storefront to redeem a physical gift or service, while the second is an online code for online stores or bookings. ",
				"Each e-voucher has limited validity redemption periods. E-vouchers that are not redeemed within its validity period will be void and captured in the app’s archives.",
				"E-vouchers are non-transferrable, non-exchangeable and not available for purchase.",
				"No. The registrant of the app and owner of the mobile phone has to redeem his or her received e-vouchers.",
				"Users can write in to nparks_programmes_events@nparks.gov.sg or submit a feedback via the app: Me->Settings->Report a Problem",
			];
			echo FaqWidget::widget(['header' => 'Rewards', "label" => $label, "content" => $content]); 




			$label = [
				"How do I navigate to the Coast-to-Coast Trail?",
				"What if I do not have the OneMap app in my phone?",
				"Can I use the Coast-to-Coast app as a navigation tool to other parts of Singapore?",
				"Can I use other navigation app to travel to the Coast-to-Coast Trail?",
				"Why is there another route to Lower Seletar Reservoir Park (Rower’s Bay)? Is this part of the Coast-to-Coast Trail?",
			];

			$content = [
				"Users may tap and hold on any point within the trail and the app will trigger the activation of OneMap for directions and instructions.",
				"Users will be re-directed to OneMap’s mobile webpage.",
				"You may not. The app is catered for the Coast-to-Coast trail only.",
				"You may, however navigation within the trail may not be accurate.You will also not be able to participate in the rewards program.",
				"Lower Seletar Reservoir Park (Rower’s Bay) is one of the nodes for the Round Island Route. The Coast-to-Coast app aims to include the Round Island Route and expand on its coverage of park connectors in the near future.",
			];
			echo FaqWidget::widget(['header' => 'Navigation (Explore)', "label" => $label, "content" => $content]); 



			$label = [
				"What is this feature about?",
				"Who can view my photos?",
				"Where can I report inappropriate photos?",
				"Will this feature work if I have exited the Coast-to-Coast Trail?",
				"Can I remove my photos after I have uploaded them?",
				"How long will my photos be featured on the app?",
				"Can I share my photos onto personal social media platforms?",
			];

			$content = [
				"Users are encouraged to snap and share photos of their adventures in Coast-to-Coast trail via the app. This would allow other users to view your experiences as well.",
				"Anyone who is registered with the app will be able to view your photos.",
				"You may flag out photos by tapping on the top right corner of the photo and click on 'Report Post'",
				"No, it will not work as the sharing function can only be done via the app",
				"Yes you can, you can delete them under Me tab, click on the photo you wish to delete and click on the top right menu to delete.",
				"The photos are not bounded by time.",
				"Yes, users are encouraged to share photos on their social media.",
			];
			echo FaqWidget::widget(['header' => 'User-experience Sharing (Community)', "label" => $label, "content" => $content]); 





			$label = [
				"What are Checkpoints?",
				"Where can I find them?",
				"How do I know if I’ve arrived at a Checkpoint?",
				"What can I do at these Checkpoints?",
				"Can I repeat the quests?",
				"How do I access the AR scanner?",
				"Why am I told to wait when I have arrived at the following checkpoint from the previous one?",
				"Can I travel from one checkpoint to another via other forms of transport?",
				"Do I have to leave the app on while I walk from one checkpoint to the next?",
				"Are there signs to lead me to these checkpoints along the Coast-to-Coast?",
			];

			$content = [
				"Checkpoints are locations along the Coast-to-Coast trail that have been pre-designated for users to check in, receive information and to complete quests and challenges. ",
				"The Checkpoint locations are shown in the map feature of the app.",
				"Users will be able to spot a physical signboard at the Checkpoint location that contains instructions to activate certain features of the app.",
				"Users will receive information and quests to complete at Checkpoints. In doing so, users may be rewarded the Rewards points (Flowers).",
				"You can repeat the quests as many times as you wish, however Rewards points will only be awarded once every 24 hours.",
				"The AR scanner can be accessed via explore page, click on the Camera icon on the top left. Find the the ‘eye’ icon on the top right and click to scan.",
				"As we wish to encourage all users to walk the Coast-to-Coast trail, features have been added into the app to deter users from travelling at high speeds. This includes cycling, riding on personal mobility devices, driving and more.",
				"No. We highly encourage users to walk and explore the Coast-to-Coast trail.",
				"It is not necessary to leave the app on while you are walking to the next checkpoint.",
				"There will be Coast-to-Coast trail signs and each Checkpoint will be demarcated with a specific sign as well. Users can also utilise the app to direct them to the Checkpoints.",
			];
			echo FaqWidget::widget(['header' => 'Checkpoints', "label" => $label, "content" => $content]); 





			$label = [
				"What is this feature about?",
				"Does the app feature other Places of Interests in Singapore?",
			];

			$content = [
				"Places of Interests carry information on the attractions and tenants that are located within the trail. Users can find out more about these places and get directions via the app.",
				"No. It only features Places of Interests that are found within the Coast-to-Coast trail.",
			];
			echo FaqWidget::widget(['header' => 'Places of Interests (Information)', "label" => $label, "content" => $content]); 



			$label = [
				"What notifications will I receive?",
				"Can I send messages to other users?",
				"Can I unsubscribe or choose not to receive these notifications?",
			];

			$content = [
				"Users may receive updates on upcoming events, tenant promotions, invitations to participate in special activation app events and more.",
				"No, you are not able to send messages to other users. ",
				"No, registering for the app requires all users to comply with the Terms and Conditions of Use where users are not able to disable any of the features within the app.",
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
				"The app will occasionally feature special events where all users are eligible to participate. These special events may be in conjunction with festivities, national celebrations, and partner promotions.",
				"All registered users are eligible to participate in the special events.",
				"Yes, announcements are made via several channels such as NParks Facebook page, in-app notifications or more.",
				"Duration of each special events will be determined at the point of public announcement.",
				"Special events are our way to show appreciation to our users by offering attractive rewards or bonus Rewards points in exchange for users’ participation and visits to the trail. It is also a form on encouragement to increase the frequency of visits to our green spaces while at the same time lead a healthier and active lifestyle.",
			];
			echo FaqWidget::widget(['header' => 'Special Events & Challenges', "label" => $label, "content" => $content]); 
















		?>

    </div>
</div>