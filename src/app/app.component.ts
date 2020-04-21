import { Component, HostListener } from '@angular/core';
import { Router, NavigationEnd, RouterOutlet } from '@angular/router';
import { slideInAnimation } from './animations';
import { AuthService } from '../app/services/auth/auth.service';
import { SwPush } from '@angular/service-worker'
import { NotificationsService } from './services/notification/notificationService';
import { Response } from './models/response';
import { SharedService } from './services/sharedService/shared-service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css'],
  animations: [
    slideInAnimation
    // animation triggers go here
  ]
})

export class AppComponent {
  title = 'yoManejo';

  deferredPrompt: any;
  showButton: boolean = false;

  appCanBeInstalled: boolean = false;
  masTardeSelected: boolean = false;
  masTardeNotificationSelected: boolean = false;
  isEnabledSwa: boolean = false;
  notificationPermission = (Notification as any).permission;

  suscripted: boolean;
  readonly VAPID_PUBLIC_KEY = "BGl7F8lkZqntl6jPBuFdMxk64eKKL4NZKGZg0sneZ6uoWo1S0FqdRL1bRQFrTd3df4v4a2GTEKnKgsSaMf44oc4";

  constructor(private sharedService: SharedService, private router: Router, public authService: AuthService, public swPush: SwPush, public notificationService: NotificationsService) {}

  ngOnInit() {
    this.router.events.subscribe((evt) => {
      if (!(evt instanceof NavigationEnd)) {
          return;
      }
      window.scrollTo(0, 0)
    });

    const getMasTardeResult = this.getMasTarde();
    if (getMasTardeResult) {
      if (new Date().getTime() <= Number(getMasTardeResult)) {
        this.masTardeSelected = true;
      }
    }

    const getMasTardeNotificationResult = this.getMasTardeNotification();
    if (getMasTardeNotificationResult) {
      if (new Date().getTime() <= Number(getMasTardeNotificationResult)) {
        this.masTardeNotificationSelected = true;
      }
    }

    if (this.swPush.isEnabled) {
      this.isEnabledSwa = true;
    }

    this.suscripted = true;
    this.swPush.subscription.subscribe( sub => {
      if (sub) {
        this.suscripted = true;
      } else {
        this.suscripted = false;
      }
    })

    this.swPush.notificationClicks.subscribe( (notification) => {
      if (this.authService.isLoggedIn()) {
        this.sharedService.setActiveTab(1);
        this.router.navigate(['pendientes']);
      }
    })
  }

  subscribeToNotifications() {
    this.swPush.requestSubscription({
        serverPublicKey: this.VAPID_PUBLIC_KEY
    })
    .then(sub => 
      this.notificationService.addPushSubscriber(sub).subscribe( (res:Response) => {
      console.log("res de suscrpcion ", res);
      if (res.code == 0) this.suscripted = true 
      else this.suscripted = false;
    }))
    .catch(err => console.error("Could not subscribe to notifications", err));
  }

  prepareRoute(outlet: RouterOutlet) {
    return outlet && outlet.activatedRouteData && outlet.activatedRouteData['animation'];
  }

  getMasTarde() {
    return localStorage.getItem('later');
  }

  getMasTardeNotification() {
    return localStorage.getItem('laternotifications');
  }

  setMasTarde() {
    const now = new Date();
    now.setDate(now.getDate() + 7);
    localStorage.setItem('later', JSON.stringify(now.getTime()));
    this.masTardeSelected = true;
  }

  setMasTardeNotifications() {
    const now = new Date();
    now.setDate(now.getDate() + 7);
    localStorage.setItem('laternotifications', JSON.stringify(now.getTime()));
    this.masTardeNotificationSelected = true;
  }

  @HostListener('window:beforeinstallprompt', ['$event'])
    onbeforeinstallprompt(e) {
      this.appCanBeInstalled = true;
      console.log(e);
      // Prevent Chrome 67 and earlier from automatically showing the prompt
      e.preventDefault();
      // Stash the event so it can be triggered later.
      this.deferredPrompt = e;
      this.showButton = true;
    }
    addToHomeScreen() {
    // hide our user interface that shows our A2HS button
    this.showButton = false;
    // Show the prompt
    this.deferredPrompt.prompt();
    // Wait for the user to respond to the prompt
    this.deferredPrompt.userChoice
    .then((choiceResult) => {
    if (choiceResult.outcome === 'accepted') {
      console.log('User accepted the A2HS prompt');
    } else {
      console.log('User dismissed the A2HS prompt');
    }
    this.appCanBeInstalled = false;
    this.deferredPrompt = null;
  });
  }
}


