import { Component, OnInit } from '@angular/core';
import { AuthService } from 'src/app/services/auth/auth.service';
import { SharedService } from 'src/app/services/sharedService/shared-service';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Response } from 'src/app/models/response';
import { SnackbarComponent } from '../snackbar/snackbar/snackbar.component';
import { Router } from '@angular/router';
import { ActivatedRoute } from '@angular/router';
import { AppSettings } from '../../appConstants';

@Component({
  selector: 'app-change-password',
  templateUrl: './change-password.component.html',
  styleUrls: ['./change-password.component.css']
})
export class ChangePasswordComponent implements OnInit {

  newPassword: string = '';
  newPasswordRepeat: string = '';
  oldPassword: string = '';
  notMatchedPasswords: boolean = false;
  showPass: boolean = false;
  showRepeatPass: boolean = false;
  showOldPass: boolean = false;

  durationInSeconds: number = 1;
  idUsuario: number;
  
  user = {
    'idUsuario': null,
    'oldPassword': '',
    'newPassword': '',
    'token': ''
  }

  changePasswordFromLink: boolean = false;

  constructor(private authService: AuthService, private activatedRoute: ActivatedRoute, private router: Router, private sharedService: SharedService, private snackbar: MatSnackBar) { }

  ngOnInit() {
    if (this.activatedRoute.snapshot.params.id) {
      this.changePasswordFromLink = true;
    } else {
      this.user.idUsuario = this.sharedService.getData();
    }
  }

  changePassword() {
    this.user.oldPassword = this.oldPassword;
    this.user.newPassword = this.newPassword;

    this.authService.changePassword(this.user).subscribe( (data: Response) => {
      if (data.code == 0) {
        this.snackbar.openFromComponent(SnackbarComponent, {
          duration: this.durationInSeconds * 1100,
          data: data.data
        }).afterDismissed().subscribe( afterDismiss => {
          if (data.code == 0) {
            this.authService.refreshJWT(data.data);
            this.navigateToProfile();
          }
        });
      } else {
        this.snackbar.openFromComponent(SnackbarComponent, {
          duration: this.durationInSeconds * 1100,
          data: data
        })
      }
    })
  }

  changeForgottenPassword() {
    this.user.newPassword = this.newPassword;
    this.user.token = this.activatedRoute.snapshot.params.id;

    this.authService.changeForgottenPassword(this.user).subscribe( (data: Response) => {
      if (data.code == 0) {
        this.snackbar.openFromComponent(SnackbarComponent, {
          duration: 0.5 * 1100,
          data: data.data
        }).afterDismissed().subscribe( afterDismiss => {
          if (data.code == 0) {
            console.log("DAATA AFT", data)
            this.authService.refreshJWT(data.data);
            this.authService.refreshRT(data.data);
            AppSettings.refreshRole();
            this.router.navigate(['busqueda']);
          }
        });
      } else {
        this.snackbar.openFromComponent(SnackbarComponent, {
          duration: this.durationInSeconds * 1100,
          data: data
        })
      }
    })
  }
  
  navigateToProfile() {
    this.sharedService.destroyData();
    this.router.navigate(['perfil']);
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

  showOldPassword(){
    this.showOldPass = !this.showOldPass;
  }
}
