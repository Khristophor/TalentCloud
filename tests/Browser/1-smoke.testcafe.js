import { Selector } from "testcafe";
import { managerUser, adminUser, hrUser } from "./helpers/roles";

const HOMEPAGE = "https://talent.test";

fixture(`Smoke Tests - Hello World`).page(HOMEPAGE).meta("travis", "run");

// Skip when writing new tests
// fixture.skip(`Smoke`);

test("Hello World - Static Page Access", async t => {
  await t
    .expect(Selector("#home-heading").visible)
    .ok()
    .expect(Selector("a").withText("Login").visible)
    .ok()
    .click(Selector("a").withText("Browse Jobs"))
    .expect(Selector("section.browse__index").visible)
    .ok()
    .click(Selector("a").withText("FAQ"))
    .expect(Selector("section.faq").visible)
    .ok()
    .click(Selector("a").withText("Login"))
    .expect(Selector("form button[type=submit]").withText("Login").visible)
    .ok();
});

test("Hello World - Guest Cannot Access Profile", async t => {
  await t
    .navigateTo("/profile/about")
    .expect(Selector("form button[type=submit]").withText("Login").visible)
    .ok();
});

test("Hello World - Language Toggle", async t => {
  const frenchLink = Selector("a").withText("Français");
  const englishLink = Selector("a").withText("English");
  await t
    .click(frenchLink)
    .expect(
      Selector("p").withText(
        "Il est désormais plus facile de postuler un emploi au gouvernement.",
      ),
    )
    .ok()
    .click(englishLink)
    .expect(
      Selector("p").withText("Applying to government jobs just got easier."),
    )
    .ok();
});

test("Hello World - Applicant Portal", async t => {
  await t
    .click(Selector("a").withText("Login"))
    .typeText("#email", "applicant@test.com")
    .typeText("#password", "password")
    .click(Selector("button").withText("Login"))
    .expect(
      Selector("p").withText("Applying to government jobs just got easier.")
        .visible,
    )
    .ok()
    .expect(Selector("a").withText("My Applications").visible)
    .ok()
    .expect(Selector("a").withText("My Profile").visible)
    .ok()
    .click(Selector("a").withText("Logout"))
    .expect(Selector("a").withText("Login").visible)
    .ok();
});

test("Hello World - Manager Portal", async t => {
  await t
    .useRole(managerUser)
    .navigateTo("/manager")
    .expect(
      Selector("p").withText("Hiring for government just got easier.").visible,
    )
    .ok();
});

test("Hello World - Admin Portal", async t => {
  await t
    .useRole(adminUser)
    .navigateTo("/admin")
    .expect(Selector("h1").withText("Welcome!").visible)
    .ok();
});

test("Hello World - Applicant Job Posters", async t => {
  await t
    .expect(Selector("a").withText("Browse Jobs").visible)
    .ok()
    .click(Selector("a").withText("Browse Jobs"))
    .expect(Selector(".browse__index").visible)
    .ok()
    .click(
      Selector(".browse__index-job-card")
        .find("div")
        .withText("View Job"),
    )
    .expect(
      Selector('.job-post__apply-button[title="Log in to apply for this job."]')
        .visible,
    )
    .ok();
});

test("Hello World - Manager Job Posters", async t => {
  await t
    .useRole(managerUser)
    .navigateTo("/manager/jobs")
    .expect(Selector(".manager-poster-index").visible)
    .ok();
});

test("Hello World - HR Job Posters", async t => {
  await t
    .useRole(hrUser)
    .navigateTo("/hr/jobs")
    .expect(Selector("h1").withText("Job Index").visible)
    .ok();
});
