import { Component, OnInit} from '@angular/core';
import { LoginUser } from '../../models/LoginUser';
import { AuthService } from '../../services/auth/auth.service';
import { Router } from '@angular/router';
import { MatSnackBar } from '@angular/material';
import { Response } from 'src/app/models/response';
import { SnackbarComponent } from '../snackbar/snackbar/snackbar.component';
import { AppSettings } from '../../appConstants';
import { flipAnimation } from '../../animations';
import { BreakpointObserver } from '@angular/cdk/layout';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css'],
  animations: [
    flipAnimation
    // animation triggers go here
  ]
})
export class LoginComponent implements OnInit {

  constructor(private auth: AuthService, private router: Router, private snackbar: MatSnackBar, private breakpointObserver: BreakpointObserver) {  }

  flip: string = 'inactive';

  user = {
    password: '',
    email: ''
  };
  errorLogin: boolean;

  message: string;
  errorMsg:string;
  strongMessage: string;
  bannerClass: string;
  bannerDismiss: boolean;

  showPass:boolean;
  showRepeatPass: boolean;
  dontRememberPass:boolean;
  submitted;

  showNewPasswordBox: boolean;

  userData;
  durationInSeconds: number = 3;

  newPassword: string = '';
  newPasswordRepeat: string = '';
  notMatchedPasswords: boolean = false;

  forgotPasswordEmail: string = '';

  ngOnInit() { 
    this.showPass = false;
    this.showRepeatPass = false;
    this.dontRememberPass = false;
    this.showNewPasswordBox = false;
  }

  onSubmit(){
    let usuario = new LoginUser(this.user.email, this.user.password);
    this.auth.login(usuario).subscribe( (data: any) => {
      if (data.code == 0) { //login exitoso
        localStorage.setItem('uniqueid', data.data.jwt);
        localStorage.setItem('uniquert', data.data.rt);
        AppSettings.refreshRole();
        this.router.navigate(['busqueda']);
      } else{
        switch(data.code) {
          case 1:
          case 3: {
            this.snackbar.openFromComponent(SnackbarComponent, {
              duration: this.durationInSeconds * 1100,
              data: data
            });
          }
          break;
          case 2: { //la contraseña del usuario es la default
            this.errorMsg = "";
            this.showPass = false; 
            this.submitted = false;
            this.userData = {
              'email': this.user.email,
              'name': data.name,
              'iduser': data.iduser
            }
            this.showNewPasswordBox = true;
            this.toggleFlip();
          }
          break;
        }
        this.errorLogin = true; //login fallido
      }   
    });
  }

  goToLogin() {
    this.dontRememberPass = false;
  }

  submitFirstPassword() {
    if (!this.notMatchedPasswords) {
      let usuario = new LoginUser(this.userData.email, this.newPassword);
      this.auth.firstPasswordChange(usuario).subscribe( (data: any) => {
        if (data.code == 0) { //login exitoso
          localStorage.setItem('uniqueid', data.data.jwt);
          localStorage.setItem('uniquert', data.data.rt);
          AppSettings.refreshRole();
          this.router.navigate(['busqueda']);
        } else{
          switch(data.code) {
            case 1:
            case 3: {
              this.snackbar.openFromComponent(SnackbarComponent, {
                duration: this.durationInSeconds * 1100,
                data: data
              });
            }
            break;
            case 2: { //la contraseña del usuario es igual a la default (no la cambio)
              this.errorMsg = "";
              this.showPass = false; 
              this.submitted = false;
              this.userData = {
                'email': this.user.email,
                'name': data.name,
                'iduser': data.iduser
              }
              this.showNewPasswordBox = true;
            }
            break;
          }
          this.errorLogin = true; //login fallido
        }   
      });
    } 
  }

  checkRepeatedPasswords() {
    if (this.newPassword != '' && this.newPasswordRepeat != '') {
      if (this.newPassword === this.newPasswordRepeat) {
        this.notMatchedPasswords = false;
      } else {
        this.notMatchedPasswords = true;
      }
    } else {
      if (this.newPassword != '' && this.newPasswordRepeat == '') {
        this.notMatchedPasswords = false;
      } else {
        this.notMatchedPasswords = true;
      }
    }
  }

  showPassword(){
    this.showPass = !this.showPass;
  }

  showRepeatPassword(){
    this.showRepeatPass = !this.showRepeatPass;
  }

  dontRememberPassword(){
    this.dontRememberPass = !this.dontRememberPass;
  }
  
  showNewPassBox(){
    this.showNewPasswordBox = !this.showNewPasswordBox;
    this.toggleFlip();
  }

  toggleFlip() {
    this.flip = (this.flip == 'inactive') ? 'active' : 'inactive';
  }

  isMobile() {
    return this.breakpointObserver.isMatched('(max-width: 767px)');
  }
}
