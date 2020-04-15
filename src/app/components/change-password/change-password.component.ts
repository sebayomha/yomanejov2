import { Component, OnInit } from '@angular/core';
import { AuthService } from 'src/app/services/auth/auth.service';
import { SharedService } from 'src/app/services/sharedService/shared-service';
import { MatSnackBar } from '@angular/material';
import { Response } from 'src/app/models/response';
import { SnackbarComponent } from '../snackbar/snackbar/snackbar.component';
import { Router } from '@angular/router';

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
    'newPassword': ''
  }

  constructor(private authService: AuthService, private router: Router, private sharedService: SharedService, private snackbar: MatSnackBar) { }

  ngOnInit() {
    this.user.idUsuario = this.sharedService.getData();
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
